<?php

namespace App\Services\Telegram;

use App\Contracts\Telegram\TelegramClientInterface;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\UserTelegramAccount;
use App\Services\Attendance\AttendanceCalculationEngine;
use App\Services\Attendance\AttendanceStatisticsService;
use App\Services\Payroll\PayslipGenerator;
use Carbon\Carbon;

class TelegramCommandProcessor
{
    public function __construct(
        private readonly TelegramAccountLinkService $linkService,
        private readonly TelegramClientInterface $telegramClient,
        private readonly AttendanceCalculationEngine $calcEngine,
        private readonly AttendanceStatisticsService $statsService,
        private readonly PayslipGenerator $payslipGenerator
    ) {}

    public function process(string $chatId, string $text, ?string $username = null): bool
    {
        $text = trim($text);
        $parts = explode(' ', $text);
        $command = strtolower($parts[0] ?? '');
        $param = $parts[1] ?? null;

        if ($command === '/start') {
            if (!empty($param)) {
                try {
                    $account = $this->linkService->validateAndLinkToken($param, $chatId, null, $username);
                    $msg = "<b>Account Linked Successfully!</b>\nWelcome <b>{$account->user->name}</b>. You will now receive instant attendance alerts and payslip notifications.";
                } catch (\Throwable $e) {
                    $msg = "<b>Linking Failed:</b> " . $e->getMessage();
                }
            } else {
                $msg = "<b>Employee Portal Bot</b>\nUse <code>/start &lt;token&gt;</code> with your token from Settings -> Notifications to link your account.\nType <code>/help</code> for commands.";
            }
            return $this->telegramClient->sendMessage($chatId, $msg);
        }

        // Authenticate linked account
        $account = UserTelegramAccount::where('telegram_chat_id', $chatId)
            ->where('is_active', true)
            ->whereNotNull('verified_at')
            ->first();

        if (!$account) {
            return $this->telegramClient->sendMessage($chatId, "<b>Unauthorized:</b> Your Telegram account is not linked. Please log in to the portal and generate a link token.");
        }

        $user = $account->user;

        return match ($command) {
            '/help' => $this->handleHelp($chatId),
            '/status' => $this->handleStatus($chatId, $user),
            '/attendance' => $this->handleAttendance($chatId, $user),
            '/myhours' => $this->handleMyHours($chatId, $user),
            '/mypayslip' => $this->handleMyPayslip($chatId, $user),
            default => $this->telegramClient->sendMessage($chatId, "Unknown command. Type <code>/help</code> for available commands."),
        };
    }

    private function handleHelp(string $chatId): bool
    {
        $msg = "<b>Available Employee Commands:</b>\n\n" .
            "• <code>/status</code> - View today's current check-in/out status\n" .
            "• <code>/attendance</code> - Summary of today's attendance\n" .
            "• <code>/myhours</code> - Monthly working hours statistics\n" .
            "• <code>/mypayslip</code> - View your latest payslip details";

        return $this->telegramClient->sendMessage($chatId, $msg);
    }

    private function handleStatus(string $chatId, $user): bool
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance) {
            $msg = "<b>Today's Status:</b> Not checked in yet for {$today}.";
        } else {
            $calc = $this->calcEngine->calculateForModel($attendance);
            $msg = "<b>Today's Workday Status:</b> {$calc->status->value}\n" .
                "• Check-in: {$calc->checkIn->format('h:i A')}\n" .
                "• Check-out: " . ($calc->checkOut ? $calc->checkOut->format('h:i A') : 'Active') . "\n" .
                "• Duration: {$calc->getFormattedDuration()}";
        }

        return $this->telegramClient->sendMessage($chatId, $msg);
    }

    private function handleAttendance(string $chatId, $user): bool
    {
        return $this->handleStatus($chatId, $user);
    }

    private function handleMyHours(string $chatId, $user): bool
    {
        $stats = $this->statsService->calculateMonthlyStatistics($user->id, (int) now()->year, (int) now()->month);
        $hours = round($stats->totalActualMinutes / 60, 1);

        $msg = "<b>Monthly Summary (" . now()->format('F Y') . "):</b>\n" .
            "• Present Days: {$stats->presentCount}\n" .
            "• Absent Days: {$stats->absentCount}\n" .
            "• Total Hours: {$hours} hrs\n" .
            "• Late Minutes: {$stats->totalLateMinutes} mins\n" .
            "• Overtime Minutes: {$stats->totalOvertimeMinutes} mins";

        return $this->telegramClient->sendMessage($chatId, $msg);
    }

    private function handleMyPayslip(string $chatId, $user): bool
    {
        $latestPayroll = Payroll::with(['period', 'items'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestPayroll) {
            return $this->telegramClient->sendMessage($chatId, "No payslips found for your account.");
        }

        $payload = $this->payslipGenerator->generatePayslipPayload($latestPayroll);

        $msg = "<b>Latest Payslip Details</b>\n" .
            "• Period: {$payload['period_name']}\n" .
            "• Status: {$payload['status']}\n" .
            "• Gross Pay: {$payload['gross_pay']}\n" .
            "• Deductions: {$payload['total_deductions']}\n" .
            "• <b>Net Payable: {$payload['net_pay']}</b>";

        return $this->telegramClient->sendMessage($chatId, $msg);
    }
}
