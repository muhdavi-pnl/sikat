(function () {
    const domisiliForms = $('[data-domisili-form]');

    if (!domisiliForms.length) {
        return;
    }

    const endpoints = {
        kabupaten: '{{ route('pegawai.wilayah.kabupatens') }}',
        kecamatan: '{{ route('pegawai.wilayah.kecamatans') }}',
        kelurahan: '{{ route('pegawai.wilayah.kelurahans') }}',
    };

    function initializeSelect2(select) {
        if (!select.length || select.hasClass('select2-hidden-accessible')) {
            return;
        }

        if (typeof $.fn.select2 !== 'function') {
            return;
        }

        select.select2({
            width: '100%',
            allowClear: true,
            placeholder: select.data('placeholder') || 'Pilih data',
        });
    }

    function replaceOptions(select, items, placeholder, selectedValue) {
        const nextValue = selectedValue === null || selectedValue === undefined ? '' : String(selectedValue);

        select.empty().append($('<option>', {
            value: '',
            text: placeholder,
        }));

        $.each(items || [], function (_, item) {
            select.append($('<option>', {
                value: item.id,
                text: item.text,
            }));
        });

        select.val(nextValue).trigger('change.select2');
    }

    domisiliForms.each(function () {
        const form = $(this);
        const provinsi = form.find('[data-domisili="provinsi"]');
        const kabupaten = form.find('[data-domisili="kabupaten"]');
        const kecamatan = form.find('[data-domisili="kecamatan"]');
        const kelurahan = form.find('[data-domisili="kelurahan"]');
        const activeRequests = {
            kabupaten: null,
            kecamatan: null,
            kelurahan: null,
        };
        const requestTokens = {
            kabupaten: 0,
            kecamatan: 0,
            kelurahan: 0,
        };

        [provinsi, kabupaten, kecamatan, kelurahan].forEach(initializeSelect2);

        const placeholders = {
            kabupaten: kabupaten.data('placeholder') || '-- Pilih Kabupaten/Kota --',
            kecamatan: kecamatan.data('placeholder') || '-- Pilih Kecamatan --',
            kelurahan: kelurahan.data('placeholder') || '-- Pilih Desa / Kelurahan --',
        };

        function setEnabledState() {
            kabupaten.prop('disabled', !provinsi.val());
            kecamatan.prop('disabled', !kabupaten.val());
            kelurahan.prop('disabled', !kecamatan.val());
        }

        function clearKabupaten() {
            cancelRequest('kabupaten');
            replaceOptions(kabupaten, [], placeholders.kabupaten, '');
            kabupaten.prop('disabled', !provinsi.val());
        }

        function clearKecamatan() {
            cancelRequest('kecamatan');
            replaceOptions(kecamatan, [], placeholders.kecamatan, '');
            kecamatan.prop('disabled', !kabupaten.val());
        }

        function clearKelurahan() {
            cancelRequest('kelurahan');
            replaceOptions(kelurahan, [], placeholders.kelurahan, '');
            kelurahan.prop('disabled', !kecamatan.val());
        }

        function cancelRequest(key) {
            requestTokens[key] += 1;

            if (activeRequests[key] && activeRequests[key].readyState !== 4) {
                activeRequests[key].abort();
            }

            activeRequests[key] = null;
        }

        function loadOptions(config) {
            const parentValue = String(config.parent.val() || '');
            const requestKey = config.requestKey;

            cancelRequest(requestKey);

            if (!parentValue) {
                replaceOptions(config.target, [], config.placeholder, '');
                config.target.prop('disabled', true);

                return $.Deferred().resolve().promise();
            }

            config.target.prop('disabled', true);

            const requestToken = requestTokens[requestKey];
            const request = $.getJSON(config.url, {
                [config.param]: parentValue,
            });

            activeRequests[requestKey] = request;

            return request.done(function (response) {
                if (requestTokens[requestKey] !== requestToken) {
                    return;
                }

                replaceOptions(config.target, response.results || [], config.placeholder, config.selectedValue);
                config.target.prop('disabled', false);
            }).fail(function (_xhr, textStatus) {
                if (textStatus === 'abort' || requestTokens[requestKey] !== requestToken) {
                    return;
                }

                replaceOptions(config.target, [], config.placeholder, '');
                config.target.prop('disabled', !parentValue);
            }).always(function () {
                if (activeRequests[requestKey] === request) {
                    activeRequests[requestKey] = null;
                }
            });
        }

        function loadKabupaten(selectedValue) {
            clearKabupaten();
            clearKecamatan();
            clearKelurahan();

            return loadOptions({
                parent: provinsi,
                target: kabupaten,
                url: endpoints.kabupaten,
                param: 'provinsi_id',
                requestKey: 'kabupaten',
                placeholder: placeholders.kabupaten,
                selectedValue: selectedValue,
            }).always(setEnabledState);
        }

        function loadKecamatan(selectedValue) {
            clearKecamatan();
            clearKelurahan();

            return loadOptions({
                parent: kabupaten,
                target: kecamatan,
                url: endpoints.kecamatan,
                param: 'kabupaten_id',
                requestKey: 'kecamatan',
                placeholder: placeholders.kecamatan,
                selectedValue: selectedValue,
            }).always(setEnabledState);
        }

        function loadKelurahan(selectedValue) {
            clearKelurahan();

            return loadOptions({
                parent: kecamatan,
                target: kelurahan,
                url: endpoints.kelurahan,
                param: 'kecamatan_id',
                requestKey: 'kelurahan',
                placeholder: placeholders.kelurahan,
                selectedValue: selectedValue,
            }).always(setEnabledState);
        }

        const initialKabupaten = kabupaten.val();
        const initialKecamatan = kecamatan.val();
        const initialKelurahan = kelurahan.val();

        provinsi.off('change.select2Domisili').on('change.select2Domisili', function () {
            loadKabupaten('');
        });

        kabupaten.off('change.select2Domisili').on('change.select2Domisili', function () {
            loadKecamatan('');
        });

        kecamatan.off('change.select2Domisili').on('change.select2Domisili', function () {
            loadKelurahan('');
        });

        if (provinsi.val()) {
            loadKabupaten(initialKabupaten)
                .then(function () {
                    return kabupaten.val() ? loadKecamatan(initialKecamatan) : $.Deferred().resolve().promise();
                })
                .then(function () {
                    return kecamatan.val() ? loadKelurahan(initialKelurahan) : $.Deferred().resolve().promise();
                })
                .always(setEnabledState);
        } else {
            clearKabupaten();
            clearKecamatan();
            clearKelurahan();
            setEnabledState();
        }
    });
})();

