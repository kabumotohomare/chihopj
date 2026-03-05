# ちほプロジェクト — システム仕様書

> 作成日: 2026-03-05
> 作成者: WinLogic

---

## 目次

1. [プロジェクト概要](#1-プロジェクト概要)
2. [アクター定義](#2-アクター定義)
3. [ユーザーロール・権限体系](#3-ユーザーロール権限体系)
4. [システム構成図](#4-システム構成図)
5. [ユースケース図](#5-ユースケース図)
6. [テーブル定義](#6-テーブル定義)
7. [ER図](#7-er図)
8. [画面遷移図](#8-画面遷移図)
9. [主要機能一覧](#9-主要機能一覧)
10. [技術スタック](#10-技術スタック)

---

## 1. プロジェクト概要

地方自治体と連携した地域活性化プラットフォーム。企業が「お手伝い募集」を投稿し、ワーカー（応募者）がマッチングして地域活動に参加する仕組み。

**主な機能:**
- 企業による募集投稿・管理
- ワーカーによる募集検索・応募
- 企業⇔ワーカー間のチャット
- 管理者によるユーザー・応募管理
- 役所による統計閲覧・CSV出力

---

## 2. アクター定義

本システムには **4種類のアクター**（ユーザーロール）が存在する。

```
┌─────────────────────────────────────────────────────────┐
│                    ちほプロジェクト                        │
│                                                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌─────────┐ │
│  │ ワーカー  │  │  企業     │  │  管理者   │  │  役所   │ │
│  │ (worker) │  │(company) │  │ (admin)  │  │(munici- │ │
│  │          │  │          │  │          │  │  pal)   │ │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬────┘ │
│       │              │              │              │      │
│  募集検索・応募  募集投稿・管理  ユーザー管理    統計閲覧  │
│  チャット        チャット        応募管理        CSV出力  │
│  プロフィール    企業プロフィール 操作ログ       操作ログ  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### アクター詳細

| アクター | role値 | 認証ガード | パネル | 説明 |
|---|---|---|---|---|
| **ワーカー** | `worker` | web | — | 募集を検索・応募する個人ユーザー |
| **企業** | `company` | web | — | 募集を投稿・管理する事業者 |
| **管理者** | `admin` | admin | `/admin` | システム全体を管理（Filament Admin） |
| **役所** | `municipal` | government | `/government` | 統計閲覧・CSV出力（Filament Government） |

---

## 3. ユーザーロール・権限体系

### 3.1 ロール階層図

```
                    ┌────────────────┐
                    │  super_admin   │
                    │  (全権限)       │
                    └───────┬────────┘
                            │ Gate::before で全許可
                    ┌───────┴────────┐
                    │     admin      │
                    │  (管理者)       │
                    └───────┬────────┘
                            │
              ┌─────────────┼─────────────┐
              │             │             │
      ┌───────┴──────┐ ┌───┴───────┐ ┌───┴──────────┐
      │   municipal  │ │  company  │ │    worker    │
      │   (役所)     │ │  (企業)   │ │ (ワーカー)   │
      └──────────────┘ └───────────┘ └──────────────┘
       閲覧・CSV出力    募集投稿・管理   検索・応募
```

### 3.2 二重ロール管理

本システムでは **2つのロール管理を併用** している。

| 方式 | カラム/テーブル | 用途 |
|---|---|---|
| **ENUMロール** | `users.role` | ルーティングのミドルウェア (`role:worker`, `role:company`) |
| **Spatieロール** | `model_has_roles` テーブル | Filament パネルの権限管理（Shield） |

### 3.3 権限マトリクス（Spatie Permission）

| 権限 | super_admin | municipal | company | worker |
|---|:---:|:---:|:---:|:---:|
| User::ViewAny | ✅ | — | — | — |
| User::View | ✅ | — | — | — |
| User::Create | ✅ | — | — | — |
| User::Update | ✅ | — | — | — |
| User::Delete | ✅ | — | — | — |
| JobApplication::ViewAny | ✅ | ✅ | — | — |
| JobApplication::View | ✅ | ✅ | — | — |
| JobApplication::Create | ✅ | — | — | — |
| JobApplication::Update | ✅ | — | — | — |
| JobApplication::Delete | ✅ | — | — | — |
| Activity::ViewAny | ✅ | ✅ | — | — |
| Activity::View | ✅ | ✅ | — | — |
| Role::ViewAny〜Delete | ✅ | — | — | — |

---

## 4. システム構成図

```
┌────────────────────────────────────────────────────────────┐
│                        クライアント                         │
│                                                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐  │
│  │ ユーザー画面  │  │ Admin パネル  │  │ Government パネル │  │
│  │ (Livewire/   │  │ (Filament)   │  │ (Filament)       │  │
│  │  Volt/Flux)  │  │ /admin       │  │ /government      │  │
│  └──────┬───────┘  └──────┬───────┘  └────────┬─────────┘  │
│         │                  │                    │            │
└─────────┼──────────────────┼────────────────────┼────────────┘
          │                  │                    │
┌─────────┼──────────────────┼────────────────────┼────────────┐
│         ▼                  ▼                    ▼            │
│  ┌─────────────────────────────────────────────────────┐    │
│  │              Laravel 12 (PHP 8.2+)                  │    │
│  │                                                     │    │
│  │  ┌───────────┐ ┌───────────┐ ┌──────────────────┐  │    │
│  │  │ Fortify   │ │ Livewire  │ │ Filament v4      │  │    │
│  │  │ (認証)    │ │ (UI)      │ │ (管理パネル)     │  │    │
│  │  └───────────┘ └───────────┘ └──────────────────┘  │    │
│  │  ┌───────────┐ ┌───────────┐ ┌──────────────────┐  │    │
│  │  │ Spatie    │ │ Spatie    │ │ Filament Shield  │  │    │
│  │  │ Permission│ │ActivityLog│ │ (権限GUI)        │  │    │
│  │  └───────────┘ └───────────┘ └──────────────────┘  │    │
│  └─────────────────────┬───────────────────────────────┘    │
│                        │                                    │
│                        ▼                                    │
│              ┌──────────────────┐                           │
│              │   MySQL 8.0      │                           │
│              │   (データベース)  │                           │
│              └──────────────────┘                           │
│                     サーバー                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 5. ユースケース図

### 5.1 ワーカーのユースケース

```
                        ┌─────────────────────┐
                        │     ワーカー         │
                        │     (worker)        │
                        └──────────┬──────────┘
                                   │
             ┌─────────────────────┼─────────────────────┐
             │                     │                     │
             ▼                     ▼                     ▼
    ┌────────────────┐   ┌────────────────┐   ┌────────────────┐
    │ プロフィール    │   │  募集          │   │  チャット       │
    │ 管理           │   │  検索・応募     │   │                │
    ├────────────────┤   ├────────────────┤   ├────────────────┤
    │・新規登録      │   │・募集一覧閲覧  │   │・チャット一覧  │
    │・プロフィール  │   │・キーワード検索│   │・メッセージ    │
    │  編集          │   │・タグ絞り込み  │   │  送受信        │
    │・アイコン変更  │   │・募集詳細閲覧  │   │・既読管理      │
    │                │   │・応募          │   │                │
    │                │   │・応募一覧閲覧  │   │                │
    └────────────────┘   └────────────────┘   └────────────────┘
```

### 5.2 企業のユースケース

```
                        ┌─────────────────────┐
                        │      企業            │
                        │     (company)       │
                        └──────────┬──────────┘
                                   │
             ┌─────────────────────┼─────────────────────┐
             │                     │                     │
             ▼                     ▼                     ▼
    ┌────────────────┐   ┌────────────────┐   ┌────────────────┐
    │ 企業プロフィール│   │  募集管理       │   │  応募・チャット │
    │                │   │                │   │                │
    ├────────────────┤   ├────────────────┤   ├────────────────┤
    │・新規登録      │   │・募集作成      │   │・受信応募一覧  │
    │・プロフィール  │   │・募集編集      │   │・応募承認/却下 │
    │  編集          │   │・マイ募集一覧  │   │・チャット      │
    │・アイコン変更  │   │・募集詳細閲覧  │   │  送受信        │
    │                │   │                │   │                │
    └────────────────┘   └────────────────┘   └────────────────┘
```

### 5.3 管理者のユースケース

```
                        ┌─────────────────────┐
                        │      管理者          │
                        │   (admin/super_admin)│
                        └──────────┬──────────┘
                                   │
             ┌─────────────────────┼─────────────────────┐
             │                     │                     │
             ▼                     ▼                     ▼
    ┌────────────────┐   ┌────────────────┐   ┌────────────────┐
    │ ユーザー管理    │   │  応募管理       │   │  システム管理   │
    │                │   │                │   │                │
    ├────────────────┤   ├────────────────┤   ├────────────────┤
    │・ユーザー一覧  │   │・応募一覧      │   │・操作ログ閲覧  │
    │・ユーザー作成  │   │・応募詳細      │   │・ロール管理    │
    │・ユーザー編集  │   │・ステータス変更│   │・権限管理      │
    │・ユーザー削除  │   │・CSVダウンロード│   │                │
    │・ロール割当    │   │                │   │                │
    └────────────────┘   └────────────────┘   └────────────────┘
```

### 5.4 役所のユースケース

```
                        ┌─────────────────────┐
                        │       役所           │
                        │    (municipal)      │
                        └──────────┬──────────┘
                                   │
                    ┌──────────────┼──────────────┐
                    │              │              │
                    ▼              ▼              ▼
           ┌──────────────┐ ┌──────────┐ ┌──────────────┐
           │ 応募一覧閲覧  │ │CSV出力   │ │操作ログ閲覧  │
           │ （読取専用）  │ │          │ │（読取専用）  │
           └──────────────┘ └──────────┘ └──────────────┘
```

---

## 6. テーブル定義

### 6.1 ユーザー・認証

#### users（ユーザー）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| name | varchar | ✅ | ユーザー名 |
| email | varchar (UNIQUE) | — | メールアドレス |
| email_verified_at | timestamp | ✅ | メール検証日時 |
| password | varchar | — | パスワード（bcrypt） |
| remember_token | varchar | ✅ | ログイン保持トークン |
| two_factor_secret | text | ✅ | 2FA シークレット（暗号化） |
| two_factor_recovery_codes | text | ✅ | 2FA リカバリーコード（暗号化） |
| role | enum('company','worker','admin','municipal') | — | ユーザーロール |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

#### password_reset_tokens（パスワードリセット）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| email | varchar (PK) | — | メールアドレス |
| token | varchar | — | リセットトークン |
| created_at | timestamp | ✅ | |

#### sessions（セッション）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | varchar (PK) | — | セッションID |
| user_id | bigint (FK→users) | ✅ | ユーザーID |
| ip_address | varchar(45) | ✅ | IPアドレス |
| user_agent | text | ✅ | ブラウザ情報 |
| payload | longtext | — | セッションデータ |
| last_activity | int | — | 最終アクティビティ |

---

### 6.2 プロフィール

#### company_profiles（企業プロフィール）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| user_id | bigint (FK→users, UNIQUE) | — | 企業ユーザーID |
| icon | varchar(255) | ✅ | アイコン画像パス |
| location_id | bigint (FK→locations) | ✅ | 所在地の地域マスタ |
| address | varchar(200) | — | 所在地住所 |
| representative | varchar(50) | — | 担当者名 |
| phone_number | varchar(30) | — | 担当者連絡先 |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

#### worker_profiles（ワーカープロフィール）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| user_id | bigint (FK→users, UNIQUE) | — | ワーカーユーザーID |
| handle_name | varchar(50, UNIQUE) | — | ハンドルネーム |
| icon | varchar | ✅ | アイコン画像パス |
| gender | enum('male','female','other') | — | 性別 |
| birthdate | date | — | 生年月日 |
| message | text | ✅ | ひとことメッセージ（200文字以内） |
| birth_location_id | bigint (FK→locations) | — | 出身地 |
| current_location_1_id | bigint (FK→locations) | — | 現在のお住まい1 |
| current_location_2_id | bigint (FK→locations) | ✅ | 現在のお住まい2 |
| current_address | varchar | ✅ | 現住所 |
| phone_number | varchar(20) | ✅ | 電話番号 |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

---

### 6.3 募集・応募

#### job_posts（募集投稿）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| company_id | bigint (FK→users) | — | 企業ユーザーID |
| eyecatch | varchar(255) | ✅ | アイキャッチ画像パス |
| purpose | varchar(50) | — | 募集目的（want_to_do / need_help） |
| start_datetime | datetime | ✅ | 開始日時 |
| end_datetime | datetime | ✅ | 終了日時 |
| job_title | varchar(50) | — | やること（タイトル） |
| job_detail | text | — | 具体的な内容 |
| location | varchar(200) | ✅ | 活動場所 |
| job_type_id | bigint (FK→codes) | ✅ | 募集形態（codes.type=1） |
| want_you_ids | json | ✅ | 希望タグ（codes.type=2 の type_id 配列） |
| can_do_ids | json | ✅ | できますタグ（codes.type=3 の type_id 配列） |
| posted_at | timestamp | — | 投稿日時 |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

#### job_applications（応募）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| job_id | bigint (FK→job_posts) | — | 募集投稿ID |
| worker_id | bigint (FK→users) | — | 応募ワーカーID |
| motive | text | ✅ | 応募動機 |
| reasons | json | ✅ | 応募理由（複数選択） |
| status | enum('applied','accepted','rejected') | — | ステータス（default: applied） |
| applied_at | datetime | — | 応募日時 |
| judged_at | datetime | ✅ | 判定日時 |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

**制約:** `UNIQUE(job_id, worker_id)` — 同一ユーザーが同じ募集に重複応募不可

---

### 6.4 チャット

#### chat_rooms（チャットルーム）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| application_id | bigint (FK→job_applications, UNIQUE) | — | 応募ID（1応募=1チャットルーム） |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

#### messages（チャットメッセージ）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| chat_room_id | bigint (FK→chat_rooms) | — | チャットルームID |
| sender_id | bigint (FK→users) | — | 送信者ID |
| message | text | — | メッセージ本文 |
| is_read | boolean | — | 既読フラグ（default: false） |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

---

### 6.5 マスタデータ

#### locations（地域マスタ）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| code | varchar(10, UNIQUE) | — | 地域コード |
| prefecture | varchar(50) | — | 都道府県名 |
| city | varchar(50) | ✅ | 市区町村名 |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

**インデックス:** prefecture, [prefecture, city]

#### codes（マスタコード）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| type | bigint | — | コード種類 |
| type_id | bigint | — | コード種類内のID |
| name | varchar(255) | — | 表示名称 |
| description | text | ✅ | 補足説明 |
| sort_order | int | ✅ | 表示順 |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

**制約:** `UNIQUE(type, type_id)`

**コード種類一覧:**

| type | 内容 | 例 |
|---|---|---|
| 0 | コード種類定義 | — |
| 1 | 募集形態 | 現地で, オンラインで, どちらでも |
| 2 | 希望（want_you） | 力仕事ができる, パソコンに詳しい, etc. |
| 3 | できます（can_do） | 写真撮影, SNS発信, etc. |

---

### 6.6 管理・監査

#### activity_log（操作ログ）

| カラム | 型 | NULL | 説明 |
|---|---|:---:|---|
| id | bigint (PK) | — | |
| log_name | varchar | ✅ | ログ名 |
| description | text | — | 操作説明 |
| subject_type | varchar | ✅ | 対象モデル（polymorphic） |
| subject_id | bigint | ✅ | 対象ID |
| causer_type | varchar | ✅ | 操作者モデル（polymorphic） |
| causer_id | bigint | ✅ | 操作者ID |
| properties | json | ✅ | 変更前後の詳細 |
| event | varchar | ✅ | 操作種別（created/updated/deleted） |
| batch_uuid | varchar | ✅ | バッチ処理ID |
| created_at | timestamp | ✅ | |
| updated_at | timestamp | ✅ | |

#### permissions / roles / model_has_roles / model_has_permissions / role_has_permissions

Spatie Permission パッケージの標準テーブル。ロールと権限の多対多リレーションを管理。

---

## 7. ER図

```
┌──────────────┐       ┌──────────────────┐       ┌──────────────┐
│   locations  │       │      users       │       │    codes     │
│──────────────│       │──────────────────│       │──────────────│
│ id        PK │◄──┐   │ id            PK │       │ id        PK │
│ code      UQ │   │   │ name             │       │ type         │
│ prefecture   │   │   │ email         UQ │       │ type_id      │
│ city         │   │   │ password         │       │ name         │
└──────────────┘   │   │ role (enum)      │       │ sort_order   │
                   │   │ two_factor_*     │       └───────┬──────┘
                   │   └────────┬─────────┘               │
                   │            │                          │
          ┌────────┼────────────┼──────────────────────────┤
          │        │            │                          │
          │        │     ┌──────┴──────┐                   │
          │        │     │             │                   │
          ▼        │     ▼             ▼                   │
┌─────────────────┐│  ┌────────────────────┐               │
│company_profiles ││  │  worker_profiles   │               │
│─────────────────││  │────────────────────│               │
│ id           PK ││  │ id             PK  │               │
│ user_id   FK,UQ ││  │ user_id     FK,UQ  │               │
│ location_id  FK │┘  │ handle_name    UQ  │               │
│ address         │   │ gender (enum)      │               │
│ representative  │   │ birthdate          │               │
│ phone_number    │   │ birth_location_id FK│──┐            │
└────────┬────────┘   │ current_loc_1_id FK│──┤(→locations) │
         │            │ current_loc_2_id FK│──┘            │
         │            │ phone_number       │               │
         │            └────────────────────┘               │
         │                                                 │
         │  user_id = company_id                           │
         ▼                                                 │
┌──────────────────┐                                       │
│    job_posts     │                                       │
│──────────────────│         JSON配列で参照                 │
│ id            PK │         (type_id の配列)              │
│ company_id    FK │───────────────────────────────────────│
│ job_title        │◄── want_you_ids (json) → codes.type=2 │
│ job_detail       │◄── can_do_ids   (json) → codes.type=3 │
│ purpose          │◄── job_type_id  (FK)   → codes.type=1 │
│ location         │                                       │
│ posted_at        │                                       │
└────────┬─────────┘                                       │
         │                                                 │
         │ has many                                        │
         ▼                                                 │
┌──────────────────┐                                       │
│ job_applications │                                       │
│──────────────────│                                       │
│ id            PK │                                       │
│ job_id        FK │──→ job_posts                          │
│ worker_id     FK │──→ users                              │
│ motive           │                                       │
│ reasons   (json) │                                       │
│ status    (enum) │                                       │
│ applied_at       │                                       │
│ judged_at        │                                       │
│ UQ(job_id,       │                                       │
│    worker_id)    │                                       │
└────────┬─────────┘                                       │
         │                                                 │
         │ has one                                         │
         ▼                                                 │
┌──────────────────┐                                       │
│   chat_rooms     │                                       │
│──────────────────│                                       │
│ id            PK │                                       │
│ application_id   │                                       │
│            FK,UQ │──→ job_applications                   │
└────────┬─────────┘                                       │
         │                                                 │
         │ has many                                        │
         ▼                                                 │
┌──────────────────┐
│    messages      │
│──────────────────│
│ id            PK │
│ chat_room_id  FK │──→ chat_rooms
│ sender_id     FK │──→ users
│ message          │
│ is_read          │
└──────────────────┘
```

---

## 8. 画面遷移図

### 8.1 ワーカー画面遷移

```
  ┌─────────┐     ┌──────────┐     ┌──────────────┐
  │ トップ   │────→│ ログイン  │────→│ ダッシュボード │
  │ ページ  │     │          │     │              │
  └────┬────┘     └──────────┘     └──────┬───────┘
       │                                   │
       │          ┌────────────────────────┤
       │          │          │             │
       ▼          ▼          ▼             ▼
  ┌─────────┐ ┌────────┐ ┌────────┐ ┌──────────┐
  │ 募集一覧 │ │ プロフ │ │ 応募   │ │ チャット │
  │         │ │ 登録   │ │ 一覧   │ │ 一覧     │
  └────┬────┘ └───┬────┘ └───┬────┘ └────┬─────┘
       │          │          │            │
       ▼          ▼          ▼            ▼
  ┌─────────┐ ┌────────┐ ┌────────┐ ┌──────────┐
  │ 募集詳細 │ │ プロフ │ │ 応募   │ │ チャット │
  │ (地図)  │ │ 編集   │ │ 詳細   │ │ 画面     │
  └────┬────┘ └────────┘ └────────┘ └──────────┘
       │
       ▼
  ┌─────────┐
  │ 応募     │
  │ 確認     │
  │ ダイアログ│
  └─────────┘
```

### 8.2 企業画面遷移

```
  ┌──────────┐     ┌──────────────┐
  │ ログイン  │────→│ ダッシュボード │
  │          │     │              │
  └──────────┘     └──────┬───────┘
                          │
           ┌──────────────┼──────────────┐
           │              │              │
           ▼              ▼              ▼
     ┌──────────┐  ┌──────────┐  ┌──────────┐
     │ 企業     │  │ マイ募集 │  │ 受信応募 │
     │ プロフ   │  │ 一覧     │  │ 一覧     │
     │ 編集     │  └────┬─────┘  └────┬─────┘
     └──────────┘       │              │
                        ▼              ▼
                  ┌──────────┐  ┌──────────┐
                  │ 募集作成 │  │ チャット  │
                  │ /編集    │  │ 画面     │
                  └──────────┘  └──────────┘
```

### 8.3 管理パネル画面遷移（Admin: /admin）

```
  ┌──────────┐     ┌──────────────────────────────┐
  │ ログイン  │────→│ ダッシュボード                 │
  │ /admin   │     │                              │
  └──────────┘     └──────────────┬───────────────┘
                                  │
                   ┌──────────────┼──────────────┐
                   │              │              │
                   ▼              ▼              ▼
            ┌──────────┐  ┌──────────┐  ┌──────────┐
            │ ユーザー │  │ 応募管理 │  │ 操作ログ │
            │ 管理     │  │          │  │          │
            ├──────────┤  ├──────────┤  ├──────────┤
            │ 一覧     │  │ 一覧     │  │ 一覧     │
            │ 作成     │  │ 詳細     │  │ 詳細     │
            │ 編集     │  │ 編集     │  │          │
            │ 詳細     │  │ CSV出力  │  │          │
            └──────────┘  └──────────┘  └──────────┘
```

---

## 9. 主要機能一覧

### 9.1 認証・セキュリティ

| 機能 | 説明 | 実装 |
|---|---|---|
| ログイン/ログアウト | メール+パスワード認証 | Laravel Fortify |
| 新規登録 | ワーカー/企業ユーザー登録 | Fortify + カスタムAction |
| メール検証 | 登録時のメール確認 | Fortify MustVerifyEmail |
| パスワードリセット | メールによるリセット | Fortify |
| 2要素認証 | TOTP ベースの 2FA | Fortify TwoFactorAuthentication |
| CSRF保護 | 全フォームにトークン | Laravel 標準 |

### 9.2 募集機能

| 機能 | 説明 |
|---|---|
| 募集作成 | タイトル・内容・タグ・画像・期間を設定して投稿 |
| 募集編集 | 自分の投稿した募集を編集 |
| 募集一覧 | 全募集をカード形式で表示（タグ・目的で絞り込み可） |
| 募集詳細 | 詳細情報 + 企業所在地の地図表示（Leaflet） |
| 募集検索 | キーワード + 希望タグ + できますタグ + 募集形態で検索 |

### 9.3 応募・マッチング

| 機能 | 説明 |
|---|---|
| 応募 | 募集に対して動機・理由を添えて応募 |
| 応募一覧（ワーカー） | 自分の応募履歴を確認 |
| 受信応募一覧（企業） | 自社募集への応募を確認 |
| 応募承認/却下 | 企業が応募を承認または却下 |
| 重複防止 | 同一募集への重複応募をDB制約で防止 |

### 9.4 チャット

| 機能 | 説明 |
|---|---|
| チャットルーム | 応募ごとに1つのチャットルームを自動作成 |
| メッセージ送受信 | 企業⇔ワーカー間のテキストチャット |
| 既読管理 | 未読/既読ステータス管理 |

### 9.5 管理機能（Filament Admin）

| 機能 | 説明 |
|---|---|
| ユーザー管理 | CRUD + ロール割当 |
| 応募管理 | 一覧・詳細・ステータス変更・CSV出力 |
| 操作ログ | 全操作の閲覧（Spatie Activity Log） |
| ロール・権限管理 | Filament Shield による GUI 管理 |

### 9.6 役所機能（Filament Government）

| 機能 | 説明 |
|---|---|
| 応募閲覧 | 読取専用の応募一覧・詳細 |
| CSV出力 | 応募データのCSVダウンロード |
| 操作ログ閲覧 | 操作履歴の確認（読取専用） |

---

## 10. 技術スタック

| カテゴリ | 技術 | バージョン |
|---|---|---|
| フレームワーク | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| データベース | MySQL | 8.0 |
| フロントエンド | Livewire / Volt | 3.x |
| UIコンポーネント | Flux UI | — |
| CSSフレームワーク | Tailwind CSS | 4.x |
| 管理パネル | Filament | 4.x |
| 権限管理 | Spatie Permission + Filament Shield | — |
| 操作ログ | Spatie Activity Log | — |
| 認証 | Laravel Fortify（2FA対応） | — |
| 地図表示 | Leaflet.js + Nominatim（OpenStreetMap） | 1.9.4 |
| ビルドツール | Vite | — |
| コンテナ | Docker Compose | — |
| テスト | Pest PHP | — |
