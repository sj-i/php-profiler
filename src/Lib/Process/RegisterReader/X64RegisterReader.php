<?php

/**
 * This file is part of the sj-i/php-profiler package.
 *
 * (c) sji <sji@sj-i.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpProfiler\Lib\Process\RegisterReader;

use FFI\CInteger;

/**
 * Class X64RegisterReader
 * @package PhpProfiler\Lib\Process
 */
final class X64RegisterReader
{
    private const PTRACE_PEEKUSER = 3;
    private const PTRACE_ATTACH = 16;
    private const PTRACE_DETACH = 17;

    /** @var int */
    public const R15 = 0 * 8;

    /** @var int */
    public const R14 = 1 * 8;

    /** @var int */
    public const R13 = 2 * 8;

    /** @var int */
    public const R12 = 3 * 8;

    /** @var int */
    public const BP = 4 * 8;

    /** @var int */
    public const BX = 5 * 8;

    /** @var int */
    public const R11 = 6 * 8;

    /** @var int */
    public const R10 = 7 * 8;

    /** @var int */
    public const R9 = 8 * 8;

    /** @var int */
    public const R8 = 9 * 8;

    /** @var int */
    public const AX = 10 * 8;

    /** @var int */
    public const CX = 11 * 8;

    /** @var int */
    public const DX = 12 * 8;

    /** @var int */
    public const SI = 13 * 8;

    /** @var int */
    public const DI = 14 * 8;

    /** @var int */
    public const ORIG_AX = 15 * 8;

    /** @var int */
    public const IP = 16 * 8;

    /** @var int */
    public const CS = 17 * 8;

    /** @var int */
    public const FLAGS = 18 * 8;

    /** @var int */
    public const SP = 19 * 8;

    /** @var int */
    public const SS = 20 * 8;

    /** @var int */
    public const FS_BASE = 21 * 8;

    /** @var int */
    public const GS_BASE = 22 * 8;

    /** @var int */
    public const DS = 23 * 8;

    /** @var int */
    public const ES = 24 * 8;

    /** @var int */
    public const FS = 25 * 8;

    /** @var int */
    public const GS = 26 * 8;

    /** @var int[] */
    public const ALL_REGISTERS = [
        self::R15,
        self::R14,
        self::R13,
        self::R12,
        self::BP,
        self::BX,
        self::R11,
        self::R10,
        self::R9,
        self::R8,
        self::AX,
        self::CX,
        self::DX,
        self::SI,
        self::DI,
        self::ORIG_AX,
        self::IP,
        self::CS,
        self::FLAGS,
        self::SP,
        self::SS,
        self::FS_BASE,
        self::GS_BASE,
        self::DS,
        self::ES,
        self::FS,
        self::GS,
    ];

    private \FFI $ffi;

    public function __construct()
    {
        $this->ffi = \FFI::cdef('
           struct user_regs_struct {
               unsigned long r15;
               unsigned long r14;
               unsigned long r13;
               unsigned long r12;
               unsigned long bp;
               unsigned long bx;
               unsigned long r11;
               unsigned long r10;
               unsigned long r9;
               unsigned long r8;
               unsigned long ax;
               unsigned long cx;
               unsigned long dx;
               unsigned long si;
               unsigned long di;
               unsigned long orig_ax;
               unsigned long ip;
               unsigned long cs;
               unsigned long flags;
               unsigned long sp;
               unsigned long ss;
               unsigned long fs_base;
               unsigned long gs_base;
               unsigned long ds;
               unsigned long es;
               unsigned long fs;
               unsigned long gs;
           };
           typedef int pid_t;
           enum __ptrace_request
           {
               PTRACE_TRACEME = 0,
               PTRACE_PEEKTEXT = 1,
               PTRACE_PEEKDATA = 2,
               PTRACE_PEEKUSER = 3,
               PTRACE_POKETEXT = 4,
               PTRACE_POKEDATA = 5,
               PTRACE_POKEUSER = 6,
               PTRACE_CONT = 7,
               PTRACE_KILL = 8,
               PTRACE_SINGLESTEP = 9,
               PTRACE_GETREGS = 12,
               PTRACE_SETREGS = 13,
               PTRACE_GETFPREGS = 14,
               PTRACE_SETFPREGS = 15,
               PTRACE_ATTACH = 16,
               PTRACE_DETACH = 17,
               PTRACE_GETFPXREGS = 18,
               PTRACE_SETFPXREGS = 19,
               PTRACE_SYSCALL = 24,
               PTRACE_SETOPTIONS = 0x4200,
               PTRACE_GETEVENTMSG = 0x4201,
               PTRACE_GETSIGINFO = 0x4202,
               PTRACE_SETSIGINFO = 0x4203
           };
           long ptrace(enum __ptrace_request request, pid_t pid, void *addr, void *data);
           int errno;
       ', 'libc.so.6');
    }


    /**
     * @param int $pid
     * @param int $register
     * @return int
     * @throws RegisterReaderException
     * @psalm-param value-of<X64RegisterReader::ALL_REGISTERS> $register
     */
    public function attachAndReadOne(int $pid, int $register): int
    {
        /** @var CInteger $zero */
        $zero = $this->ffi->new('long');
        $zero->cdata = 0;
        $null = \FFI::cast('void *', $zero);
        $target_offset = $this->ffi->new('long');
        /** @var \FFI\CInteger $target_offset */
        $target_offset->cdata = $register;

        /** @var \FFI\Libc\ptrace_ffi $this->ffi */
        $attach = $this->ffi->ptrace(self::PTRACE_ATTACH, $pid, $null, $null);

        if ($attach === -1) {
            /** @var int $errno */
            $errno = $this->ffi->errno;
            if ($errno) {
                throw new RegisterReaderException("failed to attach process errno={$errno}", $errno);
            }
        }
        pcntl_waitpid($pid, $status, WUNTRACED);

        $fs = $this->ffi->ptrace(self::PTRACE_PEEKUSER, $pid, \FFI::cast('void *', $target_offset), $null);
        if ($fs === -1) {
            /** @var int $errno */
            $errno = $this->ffi->errno;
            if ($errno) {
                throw new RegisterReaderException("failed to read register errno={$errno}", $errno);
            }
        }

        $detach = $this->ffi->ptrace(self::PTRACE_DETACH, $pid, $null, $null);
        if ($detach === -1) {
            /** @var int $errno */
            $errno = $this->ffi->errno;
            if ($errno) {
                throw new RegisterReaderException("failed to detach process errno={$errno}", $errno);
            }
        }

        return $fs;
    }
}
