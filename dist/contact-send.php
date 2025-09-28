<?php
/**
 * お問い合わせメール送信PHP（ロリポップ対応版）
 * 確認画面からの送信処理を行い、メールを送信する
 */

// セキュリティ設定
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// エラーレポート設定（本番環境では無効にする）
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// セッション開始
session_start();

// 設定
const SITE_NAME = 'WEBテーラー';
const SITE_URL = 'https://webtailor.work'; // 実際のサイトURLに変更してください
const ADMIN_EMAIL = 'webtailortokyo@gmail.com'; // 管理者メールアドレス
const FROM_EMAIL = 'contact@webtailor.work'; // 送信元メールアドレス（ドメインのメールアドレス）

/**
 * 入力値のサニタイズ
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * 管理者向けメール内容生成
 */
function generate_admin_email($data) {
    $message = "WEBテーラーのサイトからお問い合わせがありました。\n\n";
    $message .= "【お問い合わせ内容】\n";
    $message .= "送信日時: " . date('Y年m月d日 H:i:s') . "\n";
    $message .= "お名前: " . $data['name'] . "\n";
    $message .= "メールアドレス: " . $data['email'] . "\n";
    
    if (!empty($data['phone'])) {
        $message .= "電話番号: " . $data['phone'] . "\n";
    }
    
    $message .= "件名: " . $data['subject'] . "\n";
    
    if (!empty($data['budget'])) {
        $message .= "ご予算: " . $data['budget'] . "\n";
    }
    
    if (!empty($data['deadline'])) {
        $message .= "希望納期: " . $data['deadline'] . "\n";
    }
    
    $message .= "\n【メッセージ・ご要望】\n";
    $message .= $data['message'] . "\n\n";
    $message .= "---\n";
    $message .= "このメールは " . SITE_NAME . " のお問い合わせフォームから送信されました。\n";
    $message .= "サイトURL: " . SITE_URL . "\n";
    $message .= "送信者IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    
    return $message;
}

/**
 * 自動返信メール内容生成
 */
function generate_auto_reply($data) {
    $message = $data['name'] . " 様\n\n";
    $message .= "この度は、WEBテーラーにお問い合わせいただき、誠にありがとうございます。\n";
    $message .= "以下の内容でお問い合わせを受け付けいたしました。\n\n";
    $message .= "【受付内容】\n";
    $message .= "受付日時: " . date('Y年m月d日 H:i:s') . "\n";
    $message .= "お名前: " . $data['name'] . "\n";
    $message .= "メールアドレス: " . $data['email'] . "\n";
    
    if (!empty($data['phone'])) {
        $message .= "電話番号: " . $data['phone'] . "\n";
    }
    
    $message .= "件名: " . $data['subject'] . "\n";
    
    if (!empty($data['budget'])) {
        $message .= "ご予算: " . $data['budget'] . "\n";
    }
    
    if (!empty($data['deadline'])) {
        $message .= "希望納期: " . $data['deadline'] . "\n";
    }
    
    $message .= "\n【メッセージ・ご要望】\n";
    $message .= $data['message'] . "\n\n";
    $message .= "内容を確認の上、数営業日以内にご返信させていただきます。\n";
    $message .= "今しばらくお待ちください。\n\n";
    $message .= "なお、このメールは自動送信されています。\n";
    $message .= "ご不明な点がございましたら、このメールに直接ご返信ください。\n\n";
    $message .= "もしお急ぎの場合は、お問い合わせフォームから再度ご連絡ください。\n\n";
    $message .= "改めまして、お問い合わせありがとうございました。\n";
    $message .= "お客様のお仕事のお手伝いができることを楽しみにしております。\n\n";
    $message .= "---\n";
    $message .= "WEBテーラー\n";
    $message .= "サイト: " . SITE_URL . "\n";
    
    return $message;
}

// CSRF対策（簡易版）
function validate_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return $_POST['csrf_token'] === $_SESSION['csrf_token'];
}

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // CSRF対策
        if (!validate_token()) {
            throw new Exception('不正なリクエストです。もう一度お試しください。');
        }
        
        // 入力データの取得とサニタイズ
        $form_data = [
            'name' => sanitize_input($_POST['name'] ?? ''),
            'email' => sanitize_input($_POST['email'] ?? ''),
            'phone' => sanitize_input($_POST['phone'] ?? ''),
            'subject' => sanitize_input($_POST['subject'] ?? ''),
            'budget' => sanitize_input($_POST['budget'] ?? ''),
            'deadline' => sanitize_input($_POST['deadline'] ?? ''),
            'message' => sanitize_input($_POST['message'] ?? ''),
            'privacy' => sanitize_input($_POST['privacy'] ?? '')
        ];
        
        // 必須項目チェック
        if (empty($form_data['name'])) {
            throw new Exception('お名前を入力してください。');
        }
        
        if (empty($form_data['email'])) {
            throw new Exception('メールアドレスを入力してください。');
        }
        
        if (empty($form_data['subject'])) {
            throw new Exception('件名を入力してください。');
        }
        
        if (empty($form_data['message'])) {
            throw new Exception('メッセージを入力してください。');
        }
        
        if ($form_data['privacy'] !== 'agree') {
            throw new Exception('プライバシーポリシーにご同意ください。');
        }
        
        // メールアドレス形式チェック
        if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('正しいメールアドレスを入力してください。');
        }
        
        // 文字数制限チェック
        if (mb_strlen($form_data['name']) > 100) {
            throw new Exception('お名前は100文字以内で入力してください。');
        }
        
        if (mb_strlen($form_data['message']) > 2000) {
            throw new Exception('メッセージは2000文字以内で入力してください。');
        }
        
        // 管理者向けメール送信
        $admin_subject = '【' . SITE_NAME . '】お問い合わせ - ' . $form_data['subject'];
        $admin_message = generate_admin_email($form_data);
        
        // 管理者向けメールヘッダー（ロリポップ対応）
        $admin_headers = "From: " . FROM_EMAIL . "\r\n";
        $admin_headers .= "Reply-To: " . $form_data['email'] . "\r\n";
        $admin_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $admin_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // 管理者向けメール送信
        $admin_mail_sent = mail(ADMIN_EMAIL, $admin_subject, $admin_message, $admin_headers);
        
        if (!$admin_mail_sent) {
            error_log('Admin mail send failed');
        }
        
        // 自動返信メール送信
        $reply_subject = '【' . SITE_NAME . '】お問い合わせありがとうございます';
        $reply_message = generate_auto_reply($form_data);
        
        // 自動返信メールヘッダー（ロリポップ対応）
        $reply_headers = "From: " . FROM_EMAIL . "\r\n";
        $reply_headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
        $reply_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $reply_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        $reply_mail_sent = mail($form_data['email'], $reply_subject, $reply_message, $reply_headers);
        
        if (!$reply_mail_sent) {
            error_log('Reply mail send failed');
        }
        
        // 送信ログ記録
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'name' => $form_data['name'],
            'email' => $form_data['email'],
            'subject' => $form_data['subject'],
            'admin_mail_sent' => $admin_mail_sent,
            'reply_mail_sent' => $reply_mail_sent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 200)
        ];
        
        // ログファイルに記録（エラーが発生しても処理を続行）
        try {
            file_put_contents(
                __DIR__ . '/logs/contact_log.txt', 
                json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", 
                FILE_APPEND | LOCK_EX
            );
        } catch (Exception $log_error) {
            error_log('Log write error: ' . $log_error->getMessage());
        }
        
        // セッションクリア
        unset($_SESSION['contact_form_data']);
        unset($_SESSION['form_submitted_at']);
        unset($_SESSION['csrf_token']);
        
        // 成功メッセージをセッションに保存
        $_SESSION['success_message'] = 'お問い合わせありがとうございました。内容を確認の上、数営業日以内にご返信いたします。';
        
        // サンクスページにリダイレクト
        header('Location: /contact/thanks');
        exit;
        
    } catch (Exception $e) {
        // エラーログに記録
        error_log('Contact send error: ' . $e->getMessage());
        
        // エラーメッセージをセッションに保存してフォームにリダイレクト
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: /contact?error=send');
        exit;
    }
} else {
    // POST以外のリクエストはフォームにリダイレクト
    header('Location: /contact');
    exit;
}
?>