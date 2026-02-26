# 2回目以降のデプロイ手順（詳細版）

## 前提条件
- ✅ SSH接続が正常に動作する
- ✅ VPS上に初回デプロイが完了している
- ✅ `.env`ファイルがVPS上に存在する
- ✅ GitリポジトリがVPS上に設定されている

## ステップ1: ローカルで変更をコミット・プッシュ

### 1-1. 変更内容を確認
```bash
# ローカル（開発環境）で実行
cd /home/laravel/camp/100_laravel/chihopj

# 変更されたファイルを確認
git status

# 変更内容を確認
git diff
```

### 1-2. 変更をステージング
```bash
# すべての変更をステージング
git add .

# または、特定のファイルのみ
git add deploy.sh DEPLOY.md
```

### 1-3. コミット
```bash
# コミットメッセージを付けてコミット
git commit -m "デプロイスクリプトと手順書を追加"

# または、より詳細なメッセージ
git commit -m "Add deployment script and documentation

- Add deploy.sh for automated deployment
- Add DEPLOY.md with detailed instructions
- Improve error handling in deployment script"
```

### 1-4. リモートリポジトリにプッシュ
```bash
# mainブランチにプッシュ
git push origin main

# または、developブランチを使用している場合
git push origin develop
```

**重要**: プッシュが成功したことを確認してください。

## ステップ2: VPSにSSH接続

```bash
# 実際のユーザー名を使用（例: root, ubuntu, admin）
ssh 実際のユーザー名@hiraizumin.com
```

接続が成功すると、VPSのシェルプロンプトが表示されます。

## ステップ3: アプリケーションディレクトリに移動

```bash
# アプリケーションディレクトリに移動
cd /var/www/chihopj

# 現在のディレクトリを確認
pwd
# 出力: /var/www/chihopj

# 現在のブランチと状態を確認
git status
```

## ステップ4: デプロイスクリプトの確認

```bash
# deploy.shが存在するか確認
ls -la deploy.sh

# 実行権限があるか確認（-rwxr-xr-x と表示されればOK）
# 実行権限がない場合、付与する
chmod +x deploy.sh
```

## ステップ5: デプロイスクリプトを実行

```bash
# デプロイスクリプトを実行
./deploy.sh
```

デプロイスクリプトは自動的に以下を実行します：
1. ✅ Gitから最新のコードを取得
2. ✅ Composer依存関係の更新
3. ✅ Node.js依存関係のインストール
4. ✅ アセットのビルド（Vite）
5. ✅ Laravelキャッシュのクリア
6. ✅ 設定の最適化（config:cache, route:cache, view:cache）
7. ✅ データベースマイグレーション
8. ✅ ストレージリンクの作成
9. ✅ ファイル権限の設定
10. ✅ PHP-FPMの再起動（オプション）

## ステップ6: デプロイ結果の確認

### 6-1. エラーの確認
```bash
# デプロイスクリプトの出力を確認
# エラーメッセージが表示されていないか確認
```

### 6-2. アプリケーションの動作確認
```bash
# ブラウザで以下にアクセス
https://hiraizumin.com/

# または、curlで確認
curl -I https://hiraizumin.com/
```

### 6-3. ログの確認（必要に応じて）
```bash
# Laravelのログを確認
tail -f /var/www/chihopj/storage/logs/laravel.log

# Nginxのエラーログを確認
sudo tail -f /var/log/nginx/chihopj-error.log
```

## トラブルシューティング

### エラー1: "Gitリポジトリが見つかりません"
**原因**: `/var/www/chihopj`がGitリポジトリではない

**解決方法**:
```bash
# Gitリポジトリを初期化（初回のみ）
cd /var/www/chihopj
git init
git remote add origin https://github.com/your-username/chihopj.git
# または
git remote add origin git@github.com:your-username/chihopj.git
```

### エラー2: "リモートブランチ 'origin/main' が見つかりません"
**原因**: リモートに該当ブランチが存在しない、またはプッシュが完了していない

**解決方法**:
```bash
# ローカルでプッシュを確認
git push origin main

# VPS上でリモートを確認
git remote -v

# リモートから最新情報を取得
git fetch origin
```

### エラー3: ".envファイルが見つかりません"
**原因**: `.env`ファイルが存在しない

**解決方法**:
```bash
# .envファイルを作成（既存の設定をバックアップ）
cp .env .env.backup

# 必要に応じて.envファイルを再作成
nano .env
```

### エラー4: "Permission denied"
**原因**: ファイル権限の問題

**解決方法**:
```bash
# 権限を設定
sudo chown -R www-data:www-data /var/www/chihopj/storage
sudo chown -R www-data:www-data /var/www/chihopj/bootstrap/cache
sudo chmod -R 775 /var/www/chihopj/storage
sudo chmod -R 775 /var/www/chihopj/bootstrap/cache
```

### エラー5: "Composerがインストールされていません"
**解決方法**:
```bash
# Composerをインストール
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### エラー6: "Node.jsがインストールされていません"
**解決方法**:
```bash
# Node.jsをインストール
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

## デプロイ後の確認チェックリスト

- [ ] アプリケーションが正常に表示される（https://hiraizumin.com/）
- [ ] ログイン機能が動作する
- [ ] データベース接続が正常（エラーが発生しない）
- [ ] アセット（CSS/JS）が正しく読み込まれる
- [ ] 画像アップロードが動作する（該当する場合）
- [ ] エラーログに問題がない（`storage/logs/laravel.log`）
- [ ] Nginxのエラーログに問題がない

## よくある質問

### Q: デプロイ中にエラーが発生したら？
A: エラーメッセージを確認し、上記のトラブルシューティングを参照してください。必要に応じて、ログファイルを確認してください。

### Q: デプロイに時間がかかりますか？
A: 通常、3-5分程度です。依存関係のインストールやアセットのビルドに時間がかかることがあります。

### Q: デプロイ中にサイトがダウンしますか？
A: 通常、数秒から数十秒の短いダウンタイムが発生する可能性があります。本番環境では、メンテナンスモードを使用することを推奨します（将来の改善点）。

### Q: ロールバックするには？
A: Gitで以前のコミットに戻すことができます：
```bash
cd /var/www/chihopj
git log  # コミット履歴を確認
git reset --hard <コミットハッシュ>  # 特定のコミットに戻す
./deploy.sh  # 再度デプロイ
```
