<?php
/**
 * お問い合わせメール送信PHP
 * 確認画面からの送信処理を行い、メールを送信する
 */

// セキュリティ設定
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// エラーレポート設定（本番環境では無効にする）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// 設定
const SITE_NAME = 'WEBテーラー';
const SITE_URL = 'https://webtailor.jp'; // 実際のサイトURLに変更

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
 * メール送信関数
 */
function send_email($to, $subject, $message, $headers) {
    // 本番環境では実際のメール送信処理を実装
    // 例: mail()関数、PHPMailer、SendGrid API等を使用
    
    // 開発環境での疑似送信処理（ログファイルに出力）
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'to' => $to,
        'subject' => $subject,
        'message' => $message,
        'headers' => $headers
    ];
    
    file_put_contents(
        'mail_log.txt', 
        json_encode($log_entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", 
        FILE_APPEND | LOCK_EX
    );
    
    return true; // 実際の実装では送信結果を返す
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
    $message .= "このメールに直接ご返信いただいても結構です。\n\n";
    $message .= "もしお急ぎの場合は、お問い合わせフォームから再度ご連絡ください。\n\n";
    $message .= "改めまして、お問い合わせありがとうございました。\n";
    $message .= "お客様のお仕事のお手伝いができることを楽しみにしております。\n\n";
    $message .= "---\n";
    $message .= "WEBテーラー\n";
    $message .= "サイト: " . SITE_URL . "\n";
    
    return $message;
}

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
        if (empty($form_data['name']) || empty($form_data['email']) || empty($form_data['message'])) {
            throw new Exception('必須項目が入力されていません。');
        }
        
        // メールアドレス形式チェック
        if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('正しいメールアドレスを入力してください。');
        }
        
        // 管理者向けメール送信
        $admin_subject = '【' . SITE_NAME . '】お問い合わせがありました - ' . $form_data['subject'];
        $admin_message = generate_admin_email($form_data);
        $admin_headers = [
            'From: ' . $form_data['email'],
            'Reply-To: ' . $form_data['email'],
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        // 管理者向けメール送信は無効化
        $admin_mail_sent = true;
        
        // 自動返信メール送信
        $reply_subject = '【' . SITE_NAME . '】お問い合わせありがとうございます';
        $reply_message = generate_auto_reply($form_data);
        $reply_headers = [
            'From: noreply@webtailor.jp',
            'Reply-To: noreply@webtailor.jp',
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        $reply_mail_sent = send_email(
            $form_data['email'],
            $reply_subject,
            $reply_message,
            implode("\r\n", $reply_headers)
        );
        
        // 送信ログ記録
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'name' => $form_data['name'],
            'email' => $form_data['email'],
            'subject' => $form_data['subject'],
            'admin_mail_sent' => $admin_mail_sent,
            'reply_mail_sent' => $reply_mail_sent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        file_put_contents(
            'contact_log.txt', 
            json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", 
            FILE_APPEND | LOCK_EX
        );
        
        // セッションクリア
        unset($_SESSION['contact_form_data']);
        unset($_SESSION['form_submitted_at']);
        
        // サンクスページにリダイレクト
        header('Location: /contact-thanks');
        exit;
        
    } catch (Exception $e) {
        // エラーログに記録
        error_log('Contact send error: ' . $e->getMessage());
        
        // エラーメッセージをセッションに保存してフォームにリダイレクト
        $_SESSION['error_message'] = 'メールの送信に失敗しました。しばらく時間をおいて再度お試しください。';
        header('Location: /contact?error=send');
        exit;
    }
} else {
    // POST以外のリクエストはフォームにリダイレクト
    header('Location: /contact');
    exit;
}
?>