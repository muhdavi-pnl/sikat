<x-app-layout>
    @push('plugins_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('page_css')
        <style>
            .select2-container {
                width: 100% !important;
            }
        </style>
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.pegawai') }}">Pegawai</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Perbarui data pegawai melalui formulir berikut.</p>

        @include('kepegawaian.pegawai._form', [
            'action' => route('kepegawaian.pegawai.update', $pegawai),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui',
            'cancelRoute' => route('kepegawaian.pegawai'),
        ])
    </div>

    @push('plugins_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    @push('page_js')
        <script>
            $(function () {
                $('.form-select2').each(function () {
                    $(this).select2({
                        width: '100%',
                        allowClear: true,
                        placeholder: $(this).data('placeholder') || 'Pilih data'
                    });
                });

                $('.form-select2-ajax').each(function () {
                    const select = $(this);
                    const textTarget = $('input[name="' + select.data('text-target') + '"]');

                    select.select2({
                        width: '100%',
                        allowClear: true,
                        placeholder: select.data('placeholder') || 'Pilih data',
                        minimumInputLength: 2,
                        language: {
                            inputTooShort: function () {
                                return 'Ketik minimal 2 karakter';
                            }
                        },
                        ajax: {
                            url: select.data('url'),
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    page: params.page || 1,
                                    current: select.data('current') || select.val()
                                };
                            },
                            processResults: function (data) {
                                return data;
                            }
                        }
                    }).on('select2:select', function (event) {
                        textTarget.val(event.params.data.text || '');
                    }).on('select2:clear', function () {
                        textTarget.val('');
                    });
                });

                @include('pegawai._domisili-script')

                function toggleKelompokPegawai() {
                    const val = $('select[name="kelompok_pegawai"]').val();
                    if (val === 'tendik' || val === 'tenaga kependidikan') {
                        $('#section-akademik-dosen').slideUp(200);
                    } else {
                        $('#section-akademik-dosen').slideDown(200);
                    }
                }
                $('select[name="kelompok_pegawai"]').on('change select2:select select2:clear', toggleKelompokPegawai);

                function toggleJabatanRangkap() {
                    const val = $('select[name="jenis_jabatan_id"]').val();
                    if (String(val) === '3') {
                        $('#wrapper-jabatan-rangkap').slideDown(200);
                    } else {
                        $('#wrapper-jabatan-rangkap').slideUp(200);
                        $('select[name="jabatan_rangkap_id"]').val(null).trigger('change');
                        $('input[name="jabatan_rangkap_id_text"]').val('');
                    }
                }
                $('select[name="jenis_jabatan_id"]').on('change select2:select select2:clear', toggleJabatanRangkap);
            });
        </script>
    @endpush
</x-app-layout>

