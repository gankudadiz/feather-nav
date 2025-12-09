<?php

namespace App\Helpers;

class CaptchaHelper
{
    private static $maxAttempts = 5;
    private static $lockoutTime = 900; // 15 minutes

    /**
     * 生成算术验证码
     */
    public static function generate(): array
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $operator = rand(0, 1) ? '+' : '-';

        // 确保结果为正数（减法时）
        if ($operator === '-' && $num1 < $num2) {
            $temp = $num1;
            $num1 = $num2;
            $num2 = $temp;
        }

        $answer = $operator === '+' ? $num1 + $num2 : $num1 - $num2;

        return [
            'question' => "{$num1} {$operator} {$num2} = ?",
            'answer' => $answer
        ];
    }

    private static function getAttempts(string $username): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['login_attempts_' . $username] ?? 0;
    }
    
    private static function getLastAttemptTime(string $username): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['login_last_attempt_' . $username] ?? 0;
    }

    /**
     * 检查是否需要验证码
     */
    public static function requiresCaptcha(string $username): bool
    {
        return self::getAttempts($username) >= 3;
    }

    /**
     * 记录登录失败
     */
    public static function recordFailure(string $username): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = 'login_attempts_' . $username;
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION['login_last_attempt_' . $username] = time();
    }

    /**
     * 清除失败记录
     */
    public static function clearFailures(string $username): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['login_attempts_' . $username]);
        unset($_SESSION['login_last_attempt_' . $username]);
    }

    /**
     * 检查是否被锁定
     */
    public static function isLocked(string $username): bool
    {
        $attempts = self::getAttempts($username);
        if ($attempts < self::$maxAttempts) {
            return false;
        }
        
        $lastAttempt = self::getLastAttemptTime($username);
        if (time() - $lastAttempt < self::$lockoutTime) {
            return true;
        }
        
        // 锁定过期，重置
        self::clearFailures($username);
        return false;
    }
}
