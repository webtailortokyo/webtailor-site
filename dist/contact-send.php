<?php
/**
 * お問い合わせメール送信PHP（シンプル版）
 */

// セキュリティ設定
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// セッション開始
session_start();

// 設定
const SITE_NAME = 'WEBテーラー';
const SITE_URL = 'https://webtailor.work';
const ADMIN_EMAIL = 'contact@webtailor.work'; // ロリポップメールアドレス
const FROM_EMAIL = 'contact@webtailor.work';
const FROM_NAME = 'WEBテーラー';

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
 * ロリポップ用メール送信関数
 */
function send_mail_lolipop($to, $subject, $message, $from_email, $from_name = '', $reply_to = '') {
    mb_language('Japanese');
    mb_internal_encoding('UTF-8');
    
    $encoded_subject = mb_encode_mimeheader($subject, 'UTF-8');
    
    if ($from_name) {
        $encoded_from_name = mb_encode_mimeheader($from_name, 'UTF-8');
        $from_header = $encoded_from_name . ' <' . $from_email . '>';
    } else {
        $from_header = $from_email;
    }
    
    $headers = [];
    $headers[] = 'From: ' . $from_header;
    
    if ($reply_to) {
        $headers[] = 'Reply-To: ' . $reply_to;
    }
    
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    return mb_send_mail($to, $encoded_subject, $message, implode("\r\n", $headers));
}

/**
 * 管理者向けメール内容生成
 */
function generate_admin_email($data) {
    $message = SITE_NAME . "のサイトからお問い合わせがありました。\n\n";
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
    $message .= "この度は、" . SITE_NAME . "にお問い合わせいただき、誠にありがとうございます。\n";
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
    $message .= "改めまして、お問い合わせありがとうございました。\n\n";
    $message .= "---\n";
    $message .= SITE_NAME . "\n";
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
        
        // バリデーション
        $errors = [];
        
        if (empty($form_data['name'])) $errors[] = 'お名前を入力してください。';
        if (empty($form_data['email'])) $errors[] = 'メールアドレスを入力してください。';
        if (empty($form_data['subject'])) $errors[] = '件名を入力してください。';
        if (empty($form_data['message'])) $errors[] = 'メッセージを入力してください。';
        
        if (!empty($form_data['email']) && !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = '正しいメールアドレスを入力してください。';
        }
        
        if (!empty($errors)) {
            throw new Exception(implode('\n', $errors));
        }
        
        // 管理者向けメール送信
        $admin_subject = '【' . SITE_NAME . '】お問い合わせ - ' . $form_data['subject'];
        $admin_message = generate_admin_email($form_data);
        
        $admin_mail_sent = send_mail_lolipop(
            ADMIN_EMAIL,
            $admin_subject,
            $admin_message,
            FROM_EMAIL,
            FROM_NAME,
            $form_data['email']
        );
        
        // 自動返信メール送信
        $reply_subject = '【' . SITE_NAME . '】お問い合わせありがとうございます';
        $reply_message = generate_auto_reply($form_data);
        
        $reply_mail_sent = send_mail_lolipop(
            $form_data['email'],
            $reply_subject,
            $reply_message,
            FROM_EMAIL,
            FROM_NAME,
            ADMIN_EMAIL
        );
        
        // 送信ログ記録
        $log_dir = __DIR__ . '/logs';
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'name' => $form_data['name'],
            'email' => $form_data['email'],
            'subject' => $form_data['subject'],
            'admin_mail_sent' => $admin_mail_sent,
            'reply_mail_sent' => $reply_mail_sent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        file_put_contents(
            $log_dir . '/contact_log.txt', 
            json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", 
            FILE_APPEND | LOCK_EX
        );
        
        // セッションクリア
        session_destroy();
        
        // サンクスページにリダイレクト
        header('Location: /contact-thanks');
        exit;
        
    } catch (Exception $e) {
        error_log('Contact send error: ' . $e->getMessage());
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: /contact?error=send');
        exit;
    }
} else {
    header('Location: /contact');
    exit;
}
?>