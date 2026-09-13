<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\DokumenPegawai;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class DokumenArsipController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::user()->id);
        if ($user->hasRole('pegawai')) {
            $dokumen_arsip = Pegawai::where('user_id', $user->id)->first();
            if ($dokumen_arsip) {
                return view('dokumen-arsip.index', [
                    'dokumen_arsip' => $dokumen_arsip,
                    'title' => 'Dokumen Arsip',
                ]);
            } else {
                alert()->info('Info', 'Anda tidak dapat mengakses halaman <strong>Dokumen</strong> karena data Anda belum lengkap!')->toHtml();
                return redirect()->route('dashboard');
            }
        } else {
            if (request()->ajax()) {
                $dokumen = DokumenPegawai::all();
                return DataTables::of($dokumen)
                    ->addIndexColumn()
                    ->addColumn('action', function ($dokumen) {
                        return '<a href="#" class="btn btn-icon btn-secondary"><i class="fas fa-user"></i></a>
                            <a href="#" class="btn btn-icon btn-primary"><i class="fas fa-edit"></i></a>';
//                            <a href="' . route("pegawai.edit", $dokumen->id) . '" class="btn btn-icon btn-danger"><i class="fas fa-trash-alt"></i></a>';
                    })
                    ->make();
            }
            return view('dokumen-arsip.pegawai', [
                'title' => 'Pegawai',
            ]);
        }
    }

    public function create()
    {
        return view('dokumen-arsip.create', [
            'dokumens' => Dokumen::all(),
            'title' => 'Unggah Dokumen Arsip',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'file' => ['required', 'file', 'mimes:pdf,jpg,png', 'min:100', 'max:700'],
            ];
            $messages = [
                'required' => ':attribute tidak boleh kosong',
                'file' => ':attribute harus berupa file',
                'mimes' => ':attribute harus berupa file dengan ekstensi :values',
                'min' => ':attribute harus berukuran minimal :min kilobytes',
                'max' => ':attribute harus berukuran maksimal :max kilobytes',
            ];
            $attributes = [
                'file' => 'File Dokumen',
            ];
            $validator = Validator::make($data, $rules, $messages, $attributes);

            if ($validator->fails()) {
                return back()->with('errors', $validator->messages()->all()[0])->withInput();
            } else {
                $pegawai = Pegawai::where('user_id', Auth::user()->id)->first();
                $pegawai_id = $pegawai->id;
                $pegawai_nip = $pegawai->nip;
                $dokumen_kode = Dokumen::findOrFail($request->dokumen_id)->kode_dokumen;
                if ($request->hasFile("file")) {
                    $dokumen_pegawai = new DokumenPegawai;
                    $dokumen_pegawai->dokumen_id = $request->dokumen_id;
                    $dokumen_pegawai->pegawai_id = $pegawai_id;
                    $dokumen_pegawai->user_id = Auth::user()->id;
                    $dokumen_pegawai->nomor = $request->nomor;
                    $dokumen_pegawai->tanggal = $request->tanggal;

                    $file = $request->file;
                    $fn_file = $dokumen_kode . "_" . $pegawai_nip . "." . strtolower($file->getClientOriginalExtension());
                    $file->move(public_path('file/'.$pegawai_nip), $fn_file);
                    $dokumen_pegawai->file = $fn_file;

                    $dokumen_pegawai->save();

                    Alert::success('Success', 'Data Berhasil Disimpan!');
                    return redirect()->route('dokumen-arsip.index');
                }
            }
        } catch (QueryException $ex){
            Alert::error('Error', 'Data Gagal Disimpan!');
            return redirect()->route('dokumen-arsip.create', $pegawai_id);
        }
    }

    public function detach($dokumen_id, $pegawai_id)
    {
        $pegawai_id_decrypt = Crypt::decryptString($pegawai_id);
        try {
            $pegawai = Pegawai::find($pegawai_id_decrypt);
            $dokumen_pegawai = DokumenPegawai::where('dokumen_id', $dokumen_id)->where('pegawai_id', $pegawai_id_decrypt)->first();
            $file = $dokumen_pegawai->file;
            if(File::exists(public_path('/file/'. $pegawai->nip .'/'. $file))){
                File::delete(public_path('/file/'. $pegawai->nip .'/'. $file));
                $pegawai->dokumen()->detach($dokumen_id);
                Alert::success('Success', 'Data Berhasil Dihapus!');
                return redirect()->route('dokumen-arsip.index');
            }else{
                Alert::error('Error', 'File gagal Dihapus!');
                return redirect()->route('dokumen-arsip.index');
            }
        } catch (QueryException $ex) {
            Alert::error('Error', 'Data Gagal Dihapus!');
            return redirect()->route('dokumen-arsip.index');
        }
    }

    public function download($file_name, $pegawai_nip){
        $pegawai_nip_decrypt = Crypt::decryptString($pegawai_nip);
        $filepath = public_path('/file/'. $pegawai_nip_decrypt .'/'. $file_name);
        return Response::download($filepath);
    }
}
