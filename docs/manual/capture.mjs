/**
 * 管理画面操作マニュアル用スクリーンショット自動撮影スクリプト
 *
 * 使い方:
 *   npx playwright test docs/manual/capture.mjs --headed
 *   または
 *   node docs/manual/capture.mjs
 *
 * 前提:
 *   - Docker Compose で開発環境が起動していること (localhost:8080)
 *   - AdminUserSeeder, MunicipalUserSeeder, DevelopmentSeeder 実行済み
 */

import { chromium } from 'playwright';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { mkdirSync } from 'fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const SCREENSHOT_DIR = join(__dirname, 'screenshots');
const BASE_URL = 'http://localhost:8080';

const ADMIN_EMAIL = 'admin@example.com';
const ADMIN_PASSWORD = 'password';
const MUNICIPAL_EMAIL = 'municipal@example.com';
const MUNICIPAL_PASSWORD = 'password';

/**
 * Laravel Debugbar を非表示にする
 *
 * @param {import('playwright').Page} page
 */
async function hideDebugbar(page) {
  await page.evaluate(() => {
    document.querySelectorAll('#phpdebugbar, .phpdebugbar, [id*="debugbar"], .sf-toolbar').forEach((el) => el.remove());
    // Filament 4 の Debugbar は iframe の場合もある
    const style = document.createElement('style');
    style.textContent = '#phpdebugbar, .phpdebugbar { display: none !important; height: 0 !important; }';
    document.head.appendChild(style);
  });
}

/**
 * 要素に赤枠を付与する
 *
 * @param {import('playwright').Page} page
 * @param {string} selector - CSSセレクタ
 */
async function highlightElement(page, selector) {
  await page.evaluate((sel) => {
    const el = document.querySelector(sel);
    if (el) {
      el.style.outline = '3px solid red';
      el.style.outlineOffset = '2px';
    }
  }, selector);
}

/**
 * 全ての赤枠をクリア
 *
 * @param {import('playwright').Page} page
 */
async function clearHighlights(page) {
  await page.evaluate(() => {
    document.querySelectorAll('*').forEach((el) => {
      el.style.outline = '';
      el.style.outlineOffset = '';
    });
  });
}

/**
 * スクリーンショットを撮影
 *
 * @param {import('playwright').Page} page
 * @param {string} subdir - 'admin' or 'municipal'
 * @param {string} name - ファイル名（拡張子なし）
 */
async function capture(page, subdir, name) {
  const dir = join(SCREENSHOT_DIR, subdir);
  mkdirSync(dir, { recursive: true });
  const path = join(dir, `${name}.png`);
  await hideDebugbar(page);
  await page.screenshot({ path, fullPage: true });
  console.log(`  captured: ${subdir}/${name}.png`);
}

/**
 * ページの読み込み完了を待機
 *
 * @param {import('playwright').Page} page
 */
async function waitForPageReady(page) {
  await page.waitForLoadState('networkidle');
  // Filament の Livewire 描画を待つ
  await page.waitForTimeout(1000);
}

// ============================================================
// 管理者パネル（/admin）のキャプチャ
// ============================================================

/**
 * 管理者パネルのスクリーンショットを撮影
 *
 * @param {import('playwright').Browser} browser
 */
async function captureAdminPanel(browser) {
  console.log('\n=== 管理者パネル ===');
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await context.newPage();

  // --- 1. ログイン画面 ---
  await page.goto(`${BASE_URL}/admin/login`);
  await waitForPageReady(page);
  await capture(page, 'admin', '01_login_empty');

  // メールアドレス入力欄をハイライト
  await highlightElement(page, 'input[type="email"]');
  await capture(page, 'admin', '02_login_email_highlight');
  await clearHighlights(page);

  // 認証情報を入力
  await page.fill('input[type="email"]', ADMIN_EMAIL);
  await page.fill('input[type="password"]', ADMIN_PASSWORD);
  await highlightElement(page, 'button[type="submit"]');
  await capture(page, 'admin', '03_login_filled');
  await clearHighlights(page);

  // ログイン実行
  await page.click('button[type="submit"]');
  await waitForPageReady(page);

  // --- 2. ダッシュボード ---
  await capture(page, 'admin', '04_dashboard');

  // --- 3. ユーザー管理一覧 ---
  // サイドバーの「ユーザー管理」をハイライト＆クリック
  const userNavSelector = 'a[href*="/admin/users"]';
  await page.waitForSelector(userNavSelector);
  await highlightElement(page, userNavSelector);
  await capture(page, 'admin', '05_sidebar_users_highlight');
  await clearHighlights(page);

  await page.click(userNavSelector);
  await waitForPageReady(page);
  await capture(page, 'admin', '06_users_list');

  // --- 4. ユーザー作成 ---
  const newUserBtnSelector = 'a[href*="/admin/users/create"]';
  try {
    await page.waitForSelector(newUserBtnSelector, { timeout: 3000 });
    await highlightElement(page, newUserBtnSelector);
    await capture(page, 'admin', '07_users_new_button_highlight');
    await clearHighlights(page);

    await page.click(newUserBtnSelector);
    await waitForPageReady(page);
    await capture(page, 'admin', '08_users_create_form');

    // 戻る
    await page.goBack();
    await waitForPageReady(page);
  } catch {
    console.log('  (ユーザー作成ボタンが見つかりません。ヘッダーアクションを検索します)');
    // Filament 4 ではヘッダーアクションの場合がある
    const headerBtn = page.locator('a:has-text("新規作成"), a:has-text("ユーザーを作成"), button:has-text("新規")').first();
    if (await headerBtn.count() > 0) {
      await headerBtn.evaluate((el) => {
        el.style.outline = '3px solid red';
        el.style.outlineOffset = '2px';
      });
      await capture(page, 'admin', '07_users_new_button_highlight');
      await headerBtn.click();
      await waitForPageReady(page);
      await capture(page, 'admin', '08_users_create_form');
      await page.goBack();
      await waitForPageReady(page);
    }
  }

  // --- 5. ユーザー詳細（最初のユーザー） ---
  const viewBtnSelector = 'table tbody tr:first-child a[href*="/admin/users/"]';
  try {
    await page.waitForSelector(viewBtnSelector, { timeout: 3000 });
    // テーブルの最初の行のリンクをクリック
    const viewLink = page.locator('table tbody tr:first-child').locator('a').first();
    if (await viewLink.count() > 0) {
      await viewLink.evaluate((el) => {
        el.style.outline = '3px solid red';
        el.style.outlineOffset = '2px';
      });
      await capture(page, 'admin', '09_users_row_highlight');
      await viewLink.click();
      await waitForPageReady(page);
      await capture(page, 'admin', '10_users_view');

      // --- 6. ユーザー編集 ---
      const editBtnLocator = page.locator('a:has-text("編集"), button:has-text("編集")').first();
      if (await editBtnLocator.count() > 0) {
        await editBtnLocator.evaluate((el) => {
          el.style.outline = '3px solid red';
          el.style.outlineOffset = '2px';
        });
        await capture(page, 'admin', '11_users_edit_button_highlight');
        await editBtnLocator.click();
        await waitForPageReady(page);
        await capture(page, 'admin', '12_users_edit_form');
      }
    }
  } catch {
    console.log('  (ユーザー詳細リンクが見つかりません。アクションボタンを検索します)');
    // Filament のテーブルアクションボタンを探す
    const actionBtn = page.locator('table tbody tr:first-child button, table tbody tr:first-child a').first();
    if (await actionBtn.count() > 0) {
      await actionBtn.evaluate((el) => {
        el.style.outline = '3px solid red';
        el.style.outlineOffset = '2px';
      });
      await capture(page, 'admin', '09_users_action_highlight');
    }
  }

  // --- 7. 応募管理一覧 ---
  await page.goto(`${BASE_URL}/admin/job-applications`);
  await waitForPageReady(page);

  const appNavSelector = 'a[href*="/admin/job-applications"]';
  await highlightElement(page, appNavSelector);
  await capture(page, 'admin', '13_sidebar_applications_highlight');
  await clearHighlights(page);
  await capture(page, 'admin', '14_applications_list');

  // --- 8. CSV ダウンロードボタン ---
  const csvBtnLocator = page.locator('button:has-text("CSV"), a:has-text("CSV")').first();
  if (await csvBtnLocator.count() > 0) {
    await csvBtnLocator.evaluate((el) => {
      el.style.outline = '3px solid red';
      el.style.outlineOffset = '2px';
    });
    await capture(page, 'admin', '15_applications_csv_highlight');
    await clearHighlights(page);
  }

  // --- 9. 応募詳細 ---
  const appViewLocator = page.locator('table tbody tr:first-child a, table tbody tr:first-child button').first();
  if (await appViewLocator.count() > 0) {
    await appViewLocator.evaluate((el) => {
      el.style.outline = '3px solid red';
      el.style.outlineOffset = '2px';
    });
    await capture(page, 'admin', '16_applications_row_highlight');
    await appViewLocator.click();
    await waitForPageReady(page);
    await capture(page, 'admin', '17_applications_view');

    // --- 10. 応募編集（ステータス変更） ---
    const appEditLocator = page.locator('a:has-text("編集"), button:has-text("編集")').first();
    if (await appEditLocator.count() > 0) {
      await appEditLocator.evaluate((el) => {
        el.style.outline = '3px solid red';
        el.style.outlineOffset = '2px';
      });
      await capture(page, 'admin', '18_applications_edit_button_highlight');
      await appEditLocator.click();
      await waitForPageReady(page);
      await capture(page, 'admin', '19_applications_edit_form');
    }
  }

  // --- 11. 操作ログ一覧 ---
  await page.goto(`${BASE_URL}/admin/activity-logs`);
  await waitForPageReady(page);
  const logNavSelector = 'a[href*="/admin/activity-logs"]';
  await highlightElement(page, logNavSelector);
  await capture(page, 'admin', '20_sidebar_logs_highlight');
  await clearHighlights(page);
  await capture(page, 'admin', '21_activity_logs_list');

  // --- 12. 操作ログ詳細 ---
  const logViewLocator = page.locator('table tbody tr:first-child a, table tbody tr:first-child button').first();
  if (await logViewLocator.count() > 0) {
    await logViewLocator.evaluate((el) => {
      el.style.outline = '3px solid red';
      el.style.outlineOffset = '2px';
    });
    await capture(page, 'admin', '22_activity_logs_row_highlight');
    await logViewLocator.click();
    await waitForPageReady(page);
    await capture(page, 'admin', '23_activity_logs_view');
  }

  await context.close();
}

// ============================================================
// 役場パネル（/municipal）のキャプチャ
// ============================================================

/**
 * 役場パネルのスクリーンショットを撮影
 *
 * @param {import('playwright').Browser} browser
 */
async function captureMunicipalPanel(browser) {
  console.log('\n=== 役場パネル ===');
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await context.newPage();

  // --- 1. ログイン画面 ---
  await page.goto(`${BASE_URL}/municipal/login`);
  await waitForPageReady(page);
  await capture(page, 'municipal', '01_login_empty');

  // メールアドレス入力欄をハイライト
  await highlightElement(page, 'input[type="email"]');
  await capture(page, 'municipal', '02_login_email_highlight');
  await clearHighlights(page);

  // 認証情報を入力
  await page.fill('input[type="email"]', MUNICIPAL_EMAIL);
  await page.fill('input[type="password"]', MUNICIPAL_PASSWORD);
  await highlightElement(page, 'button[type="submit"]');
  await capture(page, 'municipal', '03_login_filled');
  await clearHighlights(page);

  // ログイン実行
  await page.click('button[type="submit"]');
  await waitForPageReady(page);

  // --- 2. ダッシュボード ---
  await capture(page, 'municipal', '04_dashboard');

  // --- 3. 応募一覧（読取専用） ---
  const appNavSelector = 'a[href*="/municipal/job-applications"]';
  try {
    await page.waitForSelector(appNavSelector, { timeout: 3000 });
    await highlightElement(page, appNavSelector);
    await capture(page, 'municipal', '05_sidebar_applications_highlight');
    await clearHighlights(page);

    await page.click(appNavSelector);
    await waitForPageReady(page);
  } catch {
    // ダッシュボードから直接遷移
    await page.goto(`${BASE_URL}/municipal/job-applications`);
    await waitForPageReady(page);
  }
  await capture(page, 'municipal', '06_applications_list');

  // --- 4. 応募詳細（読取専用） ---
  const viewLocator = page.locator('table tbody tr:first-child a, table tbody tr:first-child button').first();
  if (await viewLocator.count() > 0) {
    await viewLocator.evaluate((el) => {
      el.style.outline = '3px solid red';
      el.style.outlineOffset = '2px';
    });
    await capture(page, 'municipal', '07_applications_row_highlight');
    await viewLocator.click();
    await waitForPageReady(page);
    await capture(page, 'municipal', '08_applications_view');
  }

  // --- 5. CSVダウンロード ---
  const csvNavSelector = 'a[href*="/municipal/csv-download"]';
  try {
    await page.waitForSelector(csvNavSelector, { timeout: 3000 });
    await highlightElement(page, csvNavSelector);
    await capture(page, 'municipal', '09_sidebar_csv_highlight');
    await clearHighlights(page);

    await page.click(csvNavSelector);
    await waitForPageReady(page);
  } catch {
    await page.goto(`${BASE_URL}/municipal/csv-download`);
    await waitForPageReady(page);
  }
  await capture(page, 'municipal', '10_csv_download_page');

  // ダウンロードボタンをハイライト
  const csvDownloadBtn = page.locator('button:has-text("CSV"), a:has-text("CSV")').first();
  if (await csvDownloadBtn.count() > 0) {
    await csvDownloadBtn.evaluate((el) => {
      el.style.outline = '3px solid red';
      el.style.outlineOffset = '2px';
    });
    await capture(page, 'municipal', '11_csv_download_button_highlight');
    await clearHighlights(page);
  }

  // --- 6. 操作ログ一覧 ---
  const logNavSelector = 'a[href*="/municipal/activity-logs"]';
  try {
    await page.waitForSelector(logNavSelector, { timeout: 3000 });
    await highlightElement(page, logNavSelector);
    await capture(page, 'municipal', '12_sidebar_logs_highlight');
    await clearHighlights(page);

    await page.click(logNavSelector);
    await waitForPageReady(page);
  } catch {
    await page.goto(`${BASE_URL}/municipal/activity-logs`);
    await waitForPageReady(page);
  }
  await capture(page, 'municipal', '13_activity_logs_list');

  // --- 7. 操作ログ詳細 ---
  const logViewLocator = page.locator('table tbody tr:first-child a, table tbody tr:first-child button').first();
  if (await logViewLocator.count() > 0) {
    await logViewLocator.evaluate((el) => {
      el.style.outline = '3px solid red';
      el.style.outlineOffset = '2px';
    });
    await capture(page, 'municipal', '14_activity_logs_row_highlight');
    await logViewLocator.click();
    await waitForPageReady(page);
    await capture(page, 'municipal', '15_activity_logs_view');
  }

  await context.close();
}

// ============================================================
// メイン実行
// ============================================================
async function main() {
  console.log('管理画面マニュアル用スクリーンショット撮影を開始します...');
  console.log(`ベースURL: ${BASE_URL}`);

  const browser = await chromium.launch({ headless: true });

  try {
    await captureAdminPanel(browser);
    await captureMunicipalPanel(browser);
    console.log('\n撮影完了!');
    console.log(`保存先: ${SCREENSHOT_DIR}/`);
  } catch (error) {
    console.error('エラーが発生しました:', error);
    process.exit(1);
  } finally {
    await browser.close();
  }
}

main();
