<?php
/**
 * SIZSR Admin - WhatsApp Cloud API Tester
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$adminPage = 'settings';
$pageTitle = 'WhatsApp API Tester';

$cred = wa_credentials();
$isConfigured = wa_enabled();

// Retrieve test result from session if redirected
$testResult = $_SESSION['whatsapp_test_result'] ?? null;
unset($_SESSION['whatsapp_test_result']);

if (is_post()) {
    csrf_check();
    
    $phone = trim((string)input('phone'));
    $message = trim((string)input('message'));
    
    if ($phone === '') {
        flash('error', 'Please enter a recipient phone number.');
        redirect('admin/whatsapp-test.php');
    }
    
    if ($message === '') {
        flash('error', 'Please enter a test message.');
        redirect('admin/whatsapp-test.php');
    }
    
    // Execute the send action with debug mode enabled
    $res = wa_send_text($phone, $message, null, true);
    
    if ($res['success']) {
        flash('success', 'Test message sent successfully!');
    } else {
        flash('error', 'Test message failed: ' . ($res['error'] ?? 'Unknown error'));
    }
    
    // Store result in session and redirect to prevent double submission
    $_SESSION['whatsapp_test_result'] = [
        'phone' => $phone,
        'normalized' => $res['to'],
        'message' => $message,
        'success' => $res['success'],
        'error' => $res['error'],
        'message_id' => $res['message_id'] ?? null,
        'debug_info' => $res['debug_info'] ?? null
    ];
    
    redirect('admin/whatsapp-test.php');
}

// Fetch the 10 most recent logs
$logs = db_all("SELECT * FROM whatsapp_logs ORDER BY id DESC LIMIT 10");

require __DIR__ . '/includes/header.php';
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <div class="flex items-center gap-2">
    <a href="<?= e(admin_url('settings.php')) ?>" class="text-sm font-semibold text-slate-500 hover:text-primary">&larr; Back to Settings</a>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 max-w-6xl">
  <!-- Left/Main side: Test Form & Debugger -->
  <div class="lg:col-span-2 space-y-6">
    
    <!-- Test Form -->
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
      <div class="flex items-center gap-2">
        <span class="grid place-items-center h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-500 shrink-0">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </span>
        <h3 class="font-bold text-slate-900 dark:text-white">Send Test Message</h3>
      </div>
      
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        
        <div>
          <label class="form-label">Recipient Phone Number</label>
          <input type="text" name="phone" required class="form-input" 
                 placeholder="e.g. +25263XXXXXXX or 63XXXXXXX" 
                 value="<?= e($testResult['phone'] ?? '') ?>">
          <p class="text-xs text-slate-400 mt-1">
            Must be added to your <strong>Test Recipients</strong> list in Meta console if using a Sandbox account.
          </p>
        </div>
        
        <div>
          <label class="form-label">Message Body</label>
          <textarea name="message" rows="4" required class="form-textarea" 
                    placeholder="Enter message text here..."><?= e($testResult['message'] ?? 'This is a test message from your SIZSR Student Registration System! 🚀') ?></textarea>
        </div>
        
        <div class="flex justify-end">
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold shadow-md shadow-emerald-500/10 flex items-center gap-2 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Send Test Message
          </button>
        </div>
      </form>
    </div>

    <!-- Debug Output (Only shows if a test result is present) -->
    <?php if ($testResult): ?>
      <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <span>API Debug Logs</span>
          <span class="px-2 py-0.5 rounded-full text-xs font-extrabold <?= $testResult['success'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400' ?>">
            <?= $testResult['success'] ? 'Success' : 'Failed' ?>
          </span>
        </h3>
        
        <div class="space-y-3 text-sm">
          <div class="grid sm:grid-cols-3 gap-2 py-1 border-b border-slate-50 dark:border-white/5">
            <span class="text-slate-400 font-medium">Recipient Number</span>
            <span class="sm:col-span-2 font-semibold text-slate-800 dark:text-slate-200"><?= e($testResult['phone']) ?></span>
          </div>
          <div class="grid sm:grid-cols-3 gap-2 py-1 border-b border-slate-50 dark:border-white/5">
            <span class="text-slate-400 font-medium">Normalized Number</span>
            <span class="sm:col-span-2 font-mono font-semibold text-slate-800 dark:text-slate-200"><?= e($testResult['normalized']) ?></span>
          </div>
          <?php if ($testResult['message_id']): ?>
            <div class="grid sm:grid-cols-3 gap-2 py-1 border-b border-slate-50 dark:border-white/5">
              <span class="text-slate-400 font-medium">Message ID</span>
              <span class="sm:col-span-2 font-mono text-xs text-slate-800 dark:text-slate-200"><?= e($testResult['message_id']) ?></span>
            </div>
          <?php endif; ?>
          <?php if ($testResult['error']): ?>
            <div class="grid sm:grid-cols-3 gap-2 py-1 border-b border-slate-50 dark:border-white/5">
              <span class="text-slate-400 font-medium">Error Message</span>
              <span class="sm:col-span-2 text-rose-500 font-semibold"><?= e($testResult['error']) ?></span>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($testResult['debug_info']): $debug = $testResult['debug_info']; ?>
          <div class="space-y-4 pt-3">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase mb-1">Request Endpoint</p>
              <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-white/5 font-mono text-xs text-slate-800 dark:text-slate-200 break-all select-all">
                POST <?= e($debug['url'] ?? 'N/A') ?>
              </div>
            </div>
            
            <?php if ($debug['payload']): ?>
              <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Request Payload</p>
                <pre class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-white/5 font-mono text-xs text-slate-800 dark:text-slate-200 overflow-x-auto select-all"><?= e(json_encode(json_decode($debug['payload']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
              </div>
            <?php endif; ?>
            
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase mb-1">HTTP Response Code</p>
              <span class="inline-block px-3 py-1 rounded-xl text-xs font-mono font-bold <?= $debug['http_code'] >= 200 && $debug['http_code'] < 300 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                HTTP <?= (int)$debug['http_code'] ?>
              </span>
            </div>
            
            <?php if ($debug['response']): ?>
              <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Response Body from Meta</p>
                <pre class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-white/5 font-mono text-xs text-slate-800 dark:text-slate-200 overflow-x-auto select-all"><?= e(json_encode(json_decode($debug['response'], true) ?: $debug['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
              </div>
            <?php endif; ?>
            
            <?php if ($debug['error']): ?>
              <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">cURL Error</p>
                <div class="p-3 bg-rose-50 dark:bg-rose-500/10 rounded-xl border border-rose-200/60 dark:border-rose-500/20 font-mono text-xs text-rose-600 dark:text-rose-400">
                  <?= e($debug['error']) ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right side: Current Config Status -->
  <div class="space-y-6">
    <div class="rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4">
      <h3 class="font-bold text-slate-900 dark:text-white">Active Configuration</h3>
      
      <div class="space-y-3.5 text-sm">
        <div class="flex justify-between items-center py-1.5 border-b border-slate-50 dark:border-white/5">
          <span class="text-slate-400">Integration Status</span>
          <span class="px-2 py-0.5 rounded-full text-xs font-bold <?= $isConfigured ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' ?>">
            <?= $isConfigured ? 'Enabled & Configured' : 'Disabled / Missing values' ?>
          </span>
        </div>
        <div class="flex justify-between items-center py-1.5 border-b border-slate-50 dark:border-white/5">
          <span class="text-slate-400">Enabled Switch</span>
          <span class="font-semibold text-slate-800 dark:text-slate-200"><?= $cred['enabled'] ? 'ON (true)' : 'OFF (false)' ?></span>
        </div>
        <div class="flex justify-between items-center py-1.5 border-b border-slate-50 dark:border-white/5">
          <span class="text-slate-400">Phone ID</span>
          <span class="font-mono font-semibold text-slate-800 dark:text-slate-200"><?= e($cred['phone_id']) ?: '<span class="text-rose-500">Not set</span>' ?></span>
        </div>
        <div class="flex justify-between items-center py-1.5 border-b border-slate-50 dark:border-white/5">
          <span class="text-slate-400">API Version</span>
          <span class="font-mono font-semibold text-slate-800 dark:text-slate-200"><?= e($cred['version']) ?></span>
        </div>
        <div class="flex justify-between items-center py-1.5 border-b border-slate-50 dark:border-white/5">
          <span class="text-slate-400">Default Country Code</span>
          <span class="font-mono font-semibold text-slate-800 dark:text-slate-200"><?= e($cred['default_cc']) ?></span>
        </div>
        <div class="flex justify-between items-center py-1.5 border-b border-slate-50 dark:border-white/5">
          <span class="text-slate-400">Access Token</span>
          <?php if ($cred['token'] !== ''): ?>
            <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">Available</span>
          <?php else: ?>
            <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800">Missing</span>
          <?php endif; ?>
        </div>
        <div class="flex justify-between items-center py-1.5">
          <span class="text-slate-400">Configuration Source</span>
          <?php
            $tokenEnv = getenv('WHATSAPP_TOKEN') !== false && getenv('WHATSAPP_TOKEN') !== '';
            $phoneEnv = getenv('WHATSAPP_PHONE_NUMBER_ID') !== false && getenv('WHATSAPP_PHONE_NUMBER_ID') !== '';
            $isEnv = $tokenEnv || $phoneEnv;
          ?>
          <span class="font-semibold <?= $isEnv ? 'text-emerald-500' : 'text-slate-600 dark:text-slate-300' ?>">
            <?= $isEnv ? '.env File' : 'Settings Dashboard' ?>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logs History -->
<div class="mt-6 rounded-2xl bg-white dark:bg-slate-800/60 border border-slate-100 dark:border-white/5 p-6 shadow-sm space-y-4 max-w-6xl">
  <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Recent WhatsApp Logs
  </h3>
  
  <?php if ($logs): ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm border-collapse">
        <thead>
          <tr class="border-b border-slate-100 dark:border-white/5 text-slate-400 font-semibold">
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Recipient</th>
            <th class="py-3 px-4">Message</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Error details</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
          <?php foreach ($logs as $l): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
              <td class="py-3 px-4 text-slate-500 whitespace-nowrap"><?= e(format_date($l['created_at'], 'M d, Y H:i')) ?></td>
              <td class="py-3 px-4 font-mono font-semibold text-slate-700 dark:text-slate-300"><?= e($l['phone']) ?></td>
              <td class="py-3 px-4 text-slate-600 dark:text-slate-300 max-w-xs truncate" title="<?= e($l['message']) ?>"><?= e($l['message']) ?></td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded-full text-xs font-bold <?= $l['status'] === 'sent' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-500/10 dark:text-rose-400' ?>">
                  <?= ucfirst($l['status']) ?>
                </span>
              </td>
              <td class="py-3 px-4 text-xs text-rose-500 max-w-xs truncate" title="<?= e($l['error'] ?? '') ?>"><?= e($l['error'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-sm text-slate-400">No logs found. Try sending a message above!</p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
