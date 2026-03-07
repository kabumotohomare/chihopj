# filament-shield + spatie/laravel-permission + activitylog 導入

## Context

Filament v4 には Laravel Admin のような組み込みのロール・権限管理UIや操作ログ機能がない。
プラグインで補完する: filament-shield（権限管理UI）+ spatie/laravel-permission（権限エンジン）+ spatie/laravel-activitylog（操作ログ）。
既存4アカウント（admin, municipal, worker, company）の権限をこれらで制御する。

## アーキテクチャ: 二層権限制御

```
第1層: canAccessPanel() + users.role ENUM（既存維持）
  └── admin → Admin パネル
  └── municipal → Government パネル

第2層: spatie/laravel-permission + filament-shield（新規追加）
  └── super_admin → 全リソース全操作
  └── municipal_viewer → Government パネルで閲覧+CSVのみ
```

**重要な設計判断:**
- `HasPanelShield` トレイトは使わない（canAccessPanel() を上書きするため）
- `HasRoles` のみ追加し、canAccessPanel() は既存の match 式を維持
- 既存フロントエンド用ポリシー（JobApplicationPolicy 等）は変更しない
- Shield の `Gate::before` で super_admin は全リソースにアクセス可能

---

## Step 1: パッケージインストール

```bash
docker compose exec app composer require \
  spatie/laravel-permission:"^6.0" \
  spatie/laravel-activitylog:"^4.9" \
  bezhansalleh/filament-shield:"^4.1"
```

## Step 2: マイグレーション・設定の公開

```bash
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
docker compose exec app php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
docker compose exec app php artisan vendor:publish --tag=filament-shield-config
docker compose exec app php artisan migrate
```

追加テーブル: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `activity_log`

## Step 3: User モデル修正

**ファイル:** `app/Models/User.php`

- `HasRoles` トレイト追加
- `LogsActivity` トレイト追加 + `getActivitylogOptions()` メソッド
- `canAccessPanel()` は既存のまま維持（HasPanelShield は使わない）

## Step 4: JobApplication / JobPost モデルに LogsActivity 追加

**ファイル:** `app/Models/JobApplication.php`, `app/Models/JobPost.php`

- `LogsActivity` トレイト追加
- `getActivitylogOptions()` で記録対象フィールドを指定

## Step 5: AdminPanelProvider に Shield プラグイン登録

**ファイル:** `app/Providers/Filament/AdminPanelProvider.php`

- `->plugins([FilamentShieldPlugin::make()])` を追加
- Government パネルには登録しない

## Step 6: Shield セットアップ

```bash
docker compose exec app php artisan shield:install
docker compose exec app php artisan shield:generate --all --panel=admin
```

## Step 7: ActivityLog 閲覧リソース作成（Admin パネル）

**新規ファイル:**
- `app/Filament/Admin/Resources/ActivityLogResource.php` — 一覧+詳細（閲覧専用）
- `app/Filament/Admin/Resources/ActivityLogResource/Pages/ListActivityLogs.php`
- `app/Filament/Admin/Resources/ActivityLogResource/Pages/ViewActivityLog.php`

内容: 操作ログのテーブル表示（日時、実行者、対象モデル、説明）+ 詳細画面（変更前後の値）

## Step 8: Government リソースに権限チェック追加

**ファイル:** `app/Filament/Government/Resources/JobApplicationResource.php`

- `canViewAny()`, `canView()` で `government.*` パーミッションをチェック
- `canCreate()`, `canEdit()`, `canDelete()` は `false` 固定

## Step 9: Seeder 修正

**新規:** `database/seeders/RoleAndPermissionSeeder.php`
- Government パネル用パーミッション作成（`government.view_any_job_application` 等）
- `municipal_viewer` ロール作成+権限付与

**修正:** `database/seeders/AdminUserSeeder.php`
- `super_admin` ロール作成+付与

**修正:** `database/seeders/MunicipalUserSeeder.php`
- `municipal_viewer` ロール付与

**修正:** `database/seeders/DatabaseSeeder.php`
- `RoleAndPermissionSeeder` を AdminUserSeeder の前に呼び出し

## Step 10: テスト作成

**新規:** `tests/Feature/ShieldPermissionTest.php`
- super_admin のアクセス確認
- municipal_viewer の権限確認
- worker/company がパネルにアクセスできないことの確認

**新規:** `tests/Feature/ActivityLogTest.php`
- User 更新時のログ記録確認
- 操作ログリソースの表示確認

---

## 検証手順

```bash
# DB リセット + 再セットアップ
docker compose exec app php artisan migrate:fresh
docker compose exec app php artisan db:seed

# テスト実行
docker compose exec app php artisan test tests/Feature/ShieldPermissionTest.php
docker compose exec app php artisan test tests/Feature/ActivityLogTest.php
docker compose exec app php artisan test tests/Feature/FilamentLoginTest.php
docker compose exec app php artisan test tests/Feature/GovernmentPanelTest.php
```

ブラウザ確認:
1. `/admin` → admin@example.com でログイン → 応募管理 + 操作ログ + Shield ロール管理が表示
2. `/government` → municipal@example.com でログイン → 応募一覧のみ（閲覧専用）
3. admin で何か操作 → 操作ログに記録されることを確認
4. Shield のロール管理画面から権限の確認・変更ができることを確認
