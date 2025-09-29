<?php
/**
 * フォームデバッグ用ファイル
 * フォームから送信されたデータを確認する
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フォームデバッグ - WEBテーラー</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; }
        .error { background-color: #f8d7da; }
        .info { background-color: #d1ecf1; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        table td, table th { padding: 8px; border: 1px solid #ddd; text-align: left; }
        table th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>📋 フォームデバッグ</h1>
    
    <div class="section info">
        <h2>このページの使い方</h2>
        <p>お問い合わせフォームの送信先を一時的にこのファイル（form-debug.php）に変更して、データが正しく送信されているか確認します。</p>
    </div>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        
        <div class="section success">
            <h2>✅ POSTデータを受信しました</h2>
            <p>送信時刻: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <div class="section">
            <h2>📨 受信したPOSTデータ</h2>
            <?php if (empty($_POST)): ?>
                <p class="error">POSTデータが空です！</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>項目名</th>
                            <th>値</th>
                            <th>データ型</th>
                            <th>文字数</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_POST as $key => $value): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                                <td><?php echo htmlspecialchars($value); ?></td>
                                <td><?php echo gettype($value); ?></td>
                                <td><?php echo mb_strlen($value); ?>文字</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>🔍 バリデーションチェック</h2>
            <?php
            $errors = [];
            
            // 必須項目チェック
            if (empty($_POST['name'])) $errors[] = 'お名前が入力されていません';
            if (empty($_POST['email'])) $errors[] = 'メールアドレスが入力されていません';
            if (empty($_POST['subject'])) $errors[] = '件名が入力されていません';
            if (empty($_POST['message'])) $errors[] = 'メッセージが入力されていません';
            
            // メールアドレス形式チェック
            if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'メールアドレスの形式が正しくありません';
            }
            
            if (empty($errors)):
            ?>
                <div class="success">
                    <p>✅ すべてのバリデーションをパスしました！</p>
                    <p>このデータであればメール送信できるはずです。</p>
                </div>
            <?php else: ?>
                <div class="error">
                    <p>❌ バリデーションエラー:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>🧪 メール送信テスト</h2>
            <?php
            // 実際にメール送信を試行
            if (empty($errors) && !empty($_POST['email'])) {
                $test_subject = '【テスト】お問い合わせフォームからのメール';
                $test_message = "フォームデバッグテストメール\n\n";
                $test_message .= "お名前: " . ($_POST['name'] ?? '') . "\n";
                $test_message .= "メールアドレス: " . ($_POST['email'] ?? '') . "\n";
                $test_message .= "件名: " . ($_POST['subject'] ?? '') . "\n";
                $test_message .= "メッセージ: " . ($_POST['message'] ?? '') . "\n";
                
                mb_language('Japanese');
                mb_internal_encoding('UTF-8');
                
                $headers = "From: contact@webtailor.work\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                $mail_result = mb_send_mail(
                    'contact@webtailor.work', // 管理者アドレス
                    $test_subject,
                    $test_message,
                    $headers
                );
                
                if ($mail_result) {
                    echo "<div class='success'><p>✅ メール送信成功！</p><p>contact@webtailor.work に送信されたか確認してください。</p></div>";
                } else {
                    echo "<div class='error'><p>❌ メール送信失敗</p></div>";
                    $error = error_get_last();
                    if ($error) {
                        echo "<pre>" . htmlspecialchars(print_r($error, true)) . "</pre>";
                    }
                }
            }
            ?>
        </div>
        
        <div class="section">
            <h2>📝 RAWデータ</h2>
            <pre><?php echo htmlspecialchars(print_r($_POST, true)); ?></pre>
        </div>
        
        <div class="section">
            <h2>🌐 リクエスト情報</h2>
            <table>
                <tr>
                    <th>REQUEST_METHOD</th>
                    <td><?php echo $_SERVER['REQUEST_METHOD']; ?></td>
                </tr>
                <tr>
                    <th>CONTENT_TYPE</th>
                    <td><?php echo $_SERVER['CONTENT_TYPE'] ?? '未設定'; ?></td>
                </tr>
                <tr>
                    <th>HTTP_REFERER</th>
                    <td><?php echo $_SERVER['HTTP_REFERER'] ?? '未設定'; ?></td>
                </tr>
                <tr>
                    <th>REMOTE_ADDR</th>
                    <td><?php echo $_SERVER['REMOTE_ADDR'] ?? '未設定'; ?></td>
                </tr>
            </table>
        </div>
        
    <?php else: ?>
        
        <div class="section info">
            <h2>ℹ️ 待機中</h2>
            <p>フォームからデータが送信されていません。</p>
            <p>お問い合わせフォームのaction属性を以下に変更してテストしてください：</p>
            <pre>action="/form-debug.php"</pre>
        </div>
        
        <div class="section">
            <h2>📋 テスト用フォーム</h2>
            <form method="POST" action="">
                <p><label>お名前: <input type="text" name="name" value="テスト太郎" required></label></p>
                <p><label>メールアドレス: <input type="email" name="email" value="test@example.com" required></label></p>
                <p><label>電話番号: <input type="tel" name="phone" value="090-1234-5678"></label></p>
                <p><label>件名: <input type="text" name="subject" value="テスト件名" required></label></p>
                <p><label>ご予算: <input type="text" name="budget" value="10万円〜30万円"></label></p>
                <p><label>希望納期: <input type="text" name="deadline" value="1ヶ月以内"></label></p>
                <p><label>メッセージ: <textarea name="message" rows="5" required>これはテストメッセージです。</textarea></label></p>
                <p><label><input type="checkbox" name="privacy" value="agree" checked> プライバシーポリシーに同意</label></p>
                <p><button type="submit">テスト送信</button></p>
            </form>
        </div>
        
    <?php endif; ?>
    
    <div class="section">
        <h2>🔧 次のステップ</h2>
        <ol>
            <li>上のテストフォームで送信テストを行う</li>
            <li>データが正しく受信されるか確認</li>
            <li>実際のお問い合わせフォームのaction属性を確認</li>
            <li>フォームのHTMLコードを確認（特にname属性）</li>
        </ol>
        
        <h3>確認すべきポイント</h3>
        <ul>
            <li>フォームの <code>method="POST"</code> が正しく設定されているか</li>
            <li>フォームの <code>action</code> が正しいPHPファイルを指しているか</li>
            <li>各input要素の <code>name</code> 属性が正しく設定されているか</li>
            <li>JavaScriptでフォーム送信を妨げていないか</li>
            <li>CSRFトークンなどの特殊な要件がないか</li>
        </ul>
    </div>
    
    <p><a href="/contact">← お問い合わせフォームに戻る</a></p>
</body>
</html>