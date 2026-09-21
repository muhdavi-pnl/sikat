<?php

namespace App\Services\Security;

use App\Models\Pegawai;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

class PegawaiDataProtectionService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Mask NIK (Nomor Induk Kependudukan): e.g., "1234567890123456" -> "1234********3456"
     */
    public function maskNik(?string $nik): string
    {
        if (blank($nik)) {
            return '-';
        }

        $length = strlen($nik);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        $prefix = substr($nik, 0, 4);
        $suffix = substr($nik, -4);
        $maskedPart = str_repeat('*', max(4, $length - 8));

        return $prefix . $maskedPart . $suffix;
    }

    /**
     * Mask NPWP: e.g., "12.345.678.9-012.000" or raw string
     */
    public function maskNpwp(?string $npwp): string
    {
        if (blank($npwp)) {
            return '-';
        }

        $clean = preg_replace('/[^0-9]/', '', $npwp);
        $length = strlen($clean);

        if ($length < 6) {
            return str_repeat('*', strlen($npwp));
        }

        $prefix = substr($clean, 0, 2);
        $suffix = substr($clean, -3);

        return $prefix . '.***.***.*-***.' . $suffix;
    }

    /**
     * Mask BPJS: e.g., "00012345678" -> "0001****678"
     */
    public function maskBpjs(?string $bpjs): string
    {
        if (blank($bpjs)) {
            return '-';
        }

        $clean = trim($bpjs);
        $length = strlen($clean);

        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        $prefix = substr($clean, 0, 4);
        $suffix = substr($clean, -3);
        $maskedPart = str_repeat('*', max(4, $length - 7));

        return $prefix . $maskedPart . $suffix;
    }

    /**
     * Mask Nomor Telepon / HP: e.g., "081234567890" -> "0812****7890"
     */
    public function maskPhone(?string $phone): string
    {
        if (blank($phone)) {
            return '-';
        }

        $clean = trim($phone);
        $length = strlen($clean);

        if ($length <= 7) {
            return str_repeat('*', $length);
        }

        $prefix = substr($clean, 0, 4);
        $suffix = substr($clean, -4);
        $masked = str_repeat('*', max(3, $length - 8));

        return $prefix . $masked . $suffix;
    }

    /**
     * Mask Email address: e.g., "john.doe@example.com" -> "j***e@example.com"
     */
    public function maskEmail(?string $email): string
    {
        if (blank($email) || ! str_contains($email, '@')) {
            return $email ?? '-';
        }

        [$name, $domain] = explode('@', $email, 2);
        $length = strlen($name);

        if ($length <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', max(2, $length - 2)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }

    /**
     * Mask Alamat Lengkap: hides specific street numbers / blocks for privacy
     */
    public function maskAlamat(?string $alamat): string
    {
        if (blank($alamat)) {
            return '-';
        }

        $trimmed = trim($alamat);
        $words = explode(' ', $trimmed);

        if (count($words) <= 2) {
            return substr($trimmed, 0, 3) . '*** [Alamat Terproteksi UU PDP]';
        }

        return $words[0] . ' ' . ($words[1] ?? '') . ' *** [Data Alamat Terproteksi UU PDP]';
    }

    /**
     * Determine if a user can view unmasked sensitive employee data.
     */
    public function canViewUnmaskedData(?User $user, ?Pegawai $pegawai): bool
    {
        if (! $user) {
            return false;
        }

        // Super-admin and kepegawaian administrators have authorized access
        if ($user->hasAnyRole(['super-admin', 'kepegawaian'])) {
            return true;
        }

        // The employee themselves can view their own data
        if ($pegawai && $user->id === $pegawai->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Log access when sensitive employee PII is accessed or unmasked.
     */
    public function logSensitiveDataAccess(Pegawai $pegawai, string $actionType, array $additionalContext = []): void
    {
        $user = Auth::user();

        $metadata = array_merge([
            'pegawai_id' => $pegawai->id,
            'pegawai_nip' => $pegawai->nip,
            'pegawai_nama' => $pegawai->nama,
            'action_type' => $actionType,
            'security_level' => 'CONFIDENTIAL_PII',
            'regulation_compliance' => 'UU_PDP_27_2022',
        ], $additionalContext);

        $this->auditService->logSecurity(
            'security.pii_access',
            "Mengakses data sensitif/PII Pegawai: {$pegawai->nama} (NIP: {$pegawai->nip}) - Tindakan: {$actionType}",
            $metadata,
            'success'
        );
    }

    /**
     * Log file / archive download access.
     */
    public function logDocumentDownload(Pegawai $pegawai, string $fileName, string $documentType = 'arsip'): void
    {
        $this->auditService->logSecurity(
            'security.document_download',
            "Mengunduh berkas arsip pegawai: {$fileName} milik {$pegawai->nama} (NIP: {$pegawai->nip})",
            [
                'pegawai_id' => $pegawai->id,
                'pegawai_nip' => $pegawai->nip,
                'file_name' => $fileName,
                'document_type' => $documentType,
            ],
            'success'
        );
    }
}
