<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use App\Support\BadgeColor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $users = User::withTrashed()
                ->with(['roles', 'pegawai'])
                ->where('id', '>', 1)
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super-admin');
                })
                ->get();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('identity', function ($user) {
                    return '<div class="font-weight-bold">' . e($user->name) . '</div>'
                        . '<div class="text-muted small">' . e($user->email) . '</div>';
                })
                ->editColumn('roles', function ($user) {
                    $roles = $user->roles->pluck('name');

                    if ($roles->isEmpty()) {
                        return '<span class="badge badge-secondary">-</span>';
                    }

                    return $roles->map(function ($roleName) {
                        return '<span class="badge ' . BadgeColor::role($roleName) . ' mr-1">' . e($roleName) . '</span>';
                    })->implode(' ');
                })
                ->addColumn('pegawai', function ($user) {
                    if ($user->pegawai) {
                        return '<div class="font-weight-bold">' . strtoupper($user->pegawai->nama) . '</div>'
                            . '<div class="text-muted small">(' . $user->pegawai->nip . ')</div>';
                    }

                    return '<span class="text-muted">Belum terhubung</span>';
                })
                ->addColumn('status', function ($user) {
                    if ($user->deleted_at) {
                        return '<span class="badge ' . BadgeColor::userStatus('non aktif') . '">Non Aktif</span>';
                    }

                    return '<span class="badge ' . BadgeColor::userStatus('aktif') . '">Aktif</span>';
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('d-m-Y');
                })
                ->addColumn('action', function ($user) {
                    $resetForm = '<form action="' . route('kepegawaian.pengguna.reset-password', $user->id) . '" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="reset" data-confirm-title="Yakin ingin mereset password pengguna ini?" data-confirm-text="Password pengguna ini akan direset ke default Sikat2019" data-confirm-item-label="Email Pengguna" data-confirm-item-name="' . e($user->email) . '" data-confirm-button="Ya, reset">'
                        . csrf_field()
                        . '<button type="submit" class="btn btn-icon btn-warning" title="Reset Password"><i class="fas fa-key"></i></button>'
                        . '</form>';

                    if ($user->deleted_at) {
                        $restoreForm = '<form action="' . route('kepegawaian.pengguna.restore', $user->id) . '" method="POST" class="d-inline">'
                            . csrf_field()
                            . '<button type="submit" class="btn btn-icon btn-info" title="Aktifkan Kembali"><i class="fas fa-redo"></i></button>'
                            . '</form>';

                        $forceDeleteForm = '<form action="' . route('kepegawaian.pengguna.force-delete', $user->id) . '" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="force-delete" data-confirm-title="Yakin ingin menghapus permanen data ini?" data-confirm-text="Data pengguna ini akan dihapus permanen dan tidak dapat dipulihkan lagi." data-confirm-item-label="Nama Pengguna" data-confirm-item-name="' . e($user->name) . '" data-confirm-button="Ya, hapus permanen">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="btn btn-icon btn-danger" title="Hapus Permanen"><i class="fas fa-trash-alt"></i></button>'
                            . '</form>';

                        return $restoreForm . ' ' . $forceDeleteForm;
                    }

                    $editLink = '<a href="' . route('kepegawaian.pengguna.edit', $user->id) . '" class="btn btn-icon btn-primary" title="Edit Pengguna"><i class="fas fa-edit"></i></a>';

                    $deactivateForm = '<form action="' . route('kepegawaian.pengguna.deactivate', $user->id) . '" method="POST" class="d-inline js-confirm-submit" data-confirm-variant="deactivate" data-confirm-title="Yakin ingin menonaktifkan data ini?" data-confirm-text="Akun pengguna ini akan dinonaktifkan." data-confirm-item-label="Nama Pengguna" data-confirm-item-name="' . e($user->name) . '" data-confirm-button="Ya, nonaktifkan">'
                        . csrf_field() . method_field('DELETE')
                        . '<button type="submit" class="btn btn-icon btn-danger" title="Non Aktifkan"><i class="fas fa-user-slash"></i></button>'
                        . '</form>';

                    return $resetForm . ' ' . $editLink . ' ' . $deactivateForm;
                })
                ->escapeColumns([])
                ->make();
        }
        return view('kepegawaian.pengguna.index', [
            'title' => 'Pengguna',
        ]);
    }

    public function searchPegawais(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $currentUserId = (int) $request->get('current_user_id', 0);
        $page = max((int) $request->get('page', 1), 1);
        $perPage = 15;

        if (mb_strlen($search) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $query = Pegawai::query()
            ->where(function ($query) use ($currentUserId) {
                $query->whereNull('user_id');

                if ($currentUserId > 0) {
                    $query->orWhere('user_id', $currentUserId);
                }
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('nama', 'like', '%' . $search . '%')
                        ->orWhere('nip', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('nama');

        $total = (clone $query)->count();

        $pegawais = $query
            ->forPage($page, $perPage)
            ->get(['id', 'nama', 'nip', 'email']);

        return response()->json([
            'results' => $pegawais->map(function ($pegawai) {
                return [
                    'id' => $pegawai->id,
                    'text' => strtoupper($pegawai->nama) . ' (' . $pegawai->nip . ')',
                    'nama' => $pegawai->nama,
                    'nip' => $pegawai->nip,
                    'email' => $pegawai->email ?? '',
                ];
            })->values(),
            'pagination' => [
                'more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $oldPegawaiId = old('pegawai_id');

        return view('kepegawaian.pengguna.create', [
            'title' => 'Tambah Pengguna',
            'roles' => $this->assignableRoles(),
            'selectedPegawai' => $oldPegawaiId ? Pegawai::find($oldPegawaiId) : null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->availableRoles())],
            'pegawai_id' => ['required', 'exists:pegawais,id'],
        ]);

        try {
            $selectedPegawai = Pegawai::findOrFail($validated['pegawai_id']);

            $user = User::create([
                'name' => $selectedPegawai->nama,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if ($this->hasPermissionRolesTable()) {
                $user->syncRoles([$validated['role']]);
            }
            $this->syncPegawaiLink($user, $validated['pegawai_id'] ?? null);

            alert()->success('Success', 'Data pengguna berhasil disimpan!');

            return redirect()->route('kepegawaian.pengguna');
        } catch (QueryException $exception) {
            alert()->error('Error', 'Data pengguna gagal disimpan!');

            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        abort_if($user->hasRole('super-admin'), 404);

        $selectedPegawai = $user->pegawai;

        $oldPegawaiId = old('pegawai_id');
        if ($oldPegawaiId) {
            $selectedPegawai = Pegawai::find($oldPegawaiId);
        }

        return view('kepegawaian.pengguna.edit', [
            'title' => 'Edit Pengguna',
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'selectedPegawai' => $selectedPegawai,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        abort_if($user->hasRole('super-admin'), 404);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', Rule::in($this->availableRoles())],
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $selectedPegawai = Pegawai::findOrFail($validated['pegawai_id']);

            $payload = [
                'name' => $selectedPegawai->nama,
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $user->update($payload);
            if ($this->hasPermissionRolesTable()) {
                $user->syncRoles([$validated['role']]);
            }
            $this->syncPegawaiLink($user, $validated['pegawai_id'] ?? null);

            alert()->success('Success', 'Data pengguna berhasil diperbarui!');

            return redirect()->route('kepegawaian.pengguna');
        } catch (QueryException $exception) {
            alert()->error('Error', 'Data pengguna gagal diperbarui!');

            return back()->withInput();
        }
    }

    /**
     * Reset the password for the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(User $user)
    {
        if ($this->isProtectedUser($user)) {
            alert()->error('Error', 'User utama tidak dapat diubah melalui aksi ini.');
            return back();
        }

        $user->update([
            'password' => Hash::make('Sikat2019'),
            'must_change_password' => true,
        ]);

        app(\App\Services\AuditService::class)->logAuth(
            eventType: 'auth.password_reset',
            action: "Admin mereset password pengguna: {$user->name} ({$user->email}) ke default Sikat2019",
            user: auth()->user(),
            metadata: [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'target_user_email' => $user->email,
            ],
            status: 'success'
        );

        alert()->success('Success', 'Password berhasil direset ke default Sikat2019.');

        return back();
    }

    /**
     * Deactivate the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function deactivate(User $user)
    {
        if ($this->isProtectedUser($user)) {
            alert()->error('Error', 'User utama tidak dapat dinonaktifkan.');
            return back();
        }

        if ($user->deleted_at) {
            alert()->info('Info', 'User sudah non aktif.');
            return back();
        }

        $user->delete();

        alert()->success('Success', 'User berhasil dinonaktifkan.');

        return back();
    }

    /**
     * Restore the specified user from deletion.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $user->restore();

        alert()->success('Success', 'User berhasil diaktifkan kembali.');

        return back();
    }

    /**
     * Permanently delete the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($this->isProtectedUser($user)) {
            alert()->error('Error', 'User utama tidak dapat dihapus permanen.');
            return back();
        }

        if ($user->pegawai) {
            $user->pegawai->update(['user_id' => null]);
        }

        $user->syncRoles([]);
        $user->forceDelete();

        alert()->success('Success', 'User berhasil dihapus permanen.');

        return back();
    }

    /**
     * Sync the pegawai link for the user.
     *
     * @param  \App\Models\User  $user
     * @param  mixed  $pegawaiId
     * @return void
     * @throws \Illuminate\Database\QueryException
     */
    protected function syncPegawaiLink(User $user, $pegawaiId)
    {
        if ($user->pegawai && (string) $user->pegawai->id !== (string) $pegawaiId) {
            $user->pegawai->update(['user_id' => null]);
        }

        if ($pegawaiId) {
            $pegawai = Pegawai::findOrFail($pegawaiId);

            if ($pegawai->user_id && (int) $pegawai->user_id !== (int) $user->id) {
                throw new QueryException('', [], new \Exception('Pegawai sudah terhubung ke user lain.'));
            }

            $pegawai->update(['user_id' => $user->id]);
        }
    }

    /**
     * Check if the user is a protected user (e.g., super-admin or the currently authenticated user).
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    protected function isProtectedUser(User $user)
    {
        return auth()->id() === $user->id || $user->hasRole('super-admin');
    }

    protected function availableRoles()
    {
        return $this->assignableRoles()
            ->pluck('name')
            ->values()
            ->all();
    }

    protected function assignableRoles()
    {
        if ($this->hasPermissionRolesTable()) {
            return Role::query()
                ->where('name', '!=', 'super-admin')
                ->orderBy('name')
                ->get();
        }

        return collect(['kepegawaian', 'jurusan', 'pegawai'])
            ->map(function ($roleName) {
                return (object) ['name' => $roleName];
            });
    }

    protected function hasPermissionRolesTable(): bool
    {
        return Schema::hasTable($this->permissionRolesTableName());
    }

    protected function permissionRolesTableName(): string
    {
        return (string) config('permission.table_names.roles', 'roles');
    }
}
