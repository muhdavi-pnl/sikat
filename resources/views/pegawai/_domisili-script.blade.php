(function () {
    const regionGroups = $('[data-region-group]');
    const syncCheckboxes = $('[data-alamat-sync]');

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

    const groupControllers = {};

    regionGroups.each(function () {
        const groupEl = $(this);
        const groupName = groupEl.data('region-group') || 'domisili';
        const provinsi = groupEl.find('[data-domisili="provinsi"]');
        const kabupaten = groupEl.find('[data-domisili="kabupaten"]');
        const kecamatan = groupEl.find('[data-domisili="kecamatan"]');
        const kelurahan = groupEl.find('[data-domisili="kelurahan"]');

        const activeRequests = { kabupaten: null, kecamatan: null, kelurahan: null };
        const requestTokens = { kabupaten: 0, kecamatan: 0, kelurahan: 0 };

        [provinsi, kabupaten, kecamatan, kelurahan].forEach(initializeSelect2);

        const placeholders = {
            kabupaten: kabupaten.data('placeholder') || '-- Pilih Kabupaten/Kota --',
            kecamatan: kecamatan.data('placeholder') || '-- Pilih Kecamatan --',
            kelurahan: kelurahan.data('placeholder') || '-- Pilih Desa / Kelurahan --',
        };

        function setEnabledState() {
            if (groupEl.hasClass('is-locked-by-sync')) {
                kabupaten.prop('disabled', true);
                kecamatan.prop('disabled', true);
                kelurahan.prop('disabled', true);
                return;
            }
            kabupaten.prop('disabled', !provinsi.val());
            kecamatan.prop('disabled', !kabupaten.val());
            kelurahan.prop('disabled', !kecamatan.val());
        }

        function cancelRequest(key) {
            requestTokens[key] += 1;
            if (activeRequests[key] && activeRequests[key].readyState !== 4) {
                activeRequests[key].abort();
            }
            activeRequests[key] = null;
        }

        function clearKabupaten() {
            cancelRequest('kabupaten');
            replaceOptions(kabupaten, [], placeholders.kabupaten, '');
            setEnabledState();
        }

        function clearKecamatan() {
            cancelRequest('kecamatan');
            replaceOptions(kecamatan, [], placeholders.kecamatan, '');
            setEnabledState();
        }

        function clearKelurahan() {
            cancelRequest('kelurahan');
            replaceOptions(kelurahan, [], placeholders.kelurahan, '');
            setEnabledState();
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
                if (!groupEl.hasClass('is-locked-by-sync')) {
                    config.target.prop('disabled', false);
                }
            }).fail(function (_xhr, textStatus) {
                if (textStatus === 'abort' || requestTokens[requestKey] !== requestToken) {
                    return;
                }

                replaceOptions(config.target, [], config.placeholder, '');
                if (!groupEl.hasClass('is-locked-by-sync')) {
                    config.target.prop('disabled', !parentValue);
                }
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

        groupControllers[groupName] = {
            groupEl: groupEl,
            provinsi: provinsi,
            kabupaten: kabupaten,
            kecamatan: kecamatan,
            kelurahan: kelurahan,
            loadKabupaten: loadKabupaten,
            loadKecamatan: loadKecamatan,
            loadKelurahan: loadKelurahan,
            setEnabledState: setEnabledState,
            lock: function () {
                groupEl.addClass('is-locked-by-sync');
                groupEl.find('textarea, input').prop('readonly', true);
                provinsi.prop('disabled', true);
                kabupaten.prop('disabled', true);
                kecamatan.prop('disabled', true);
                kelurahan.prop('disabled', true);
            },
            unlock: function () {
                groupEl.removeClass('is-locked-by-sync');
                groupEl.find('textarea, input').prop('readonly', false);
                provinsi.prop('disabled', false);
                setEnabledState();
            },
        };
    });

    // Checkbox synchronization logic
    syncCheckboxes.each(function () {
        const checkbox = $(this);
        const form = checkbox.closest('form');
        const asalGroup = form.find('[data-region-group="asal"]');
        const domisiliGroup = form.find('[data-region-group="domisili"]');

        const asalTextarea = form.find('[name="alamat_asal"]');
        const domisiliTextarea = form.find('[name="alamat"]');

        function syncValues() {
            if (!checkbox.is(':checked')) {
                return;
            }

            // Sync street address
            domisiliTextarea.val(asalTextarea.val());

            const asalCtrl = groupControllers['asal'];
            const domisiliCtrl = groupControllers['domisili'];

            if (!asalCtrl || !domisiliCtrl) {
                return;
            }

            const provVal = asalCtrl.provinsi.val();
            const kabVal = asalCtrl.kabupaten.val();
            const kecVal = asalCtrl.kecamatan.val();
            const kelVal = asalCtrl.kelurahan.val();

            domisiliCtrl.provinsi.val(provVal).trigger('change.select2');

            if (provVal) {
                domisiliCtrl.loadKabupaten(kabVal).then(function () {
                    if (kabVal) {
                        return domisiliCtrl.loadKecamatan(kecVal);
                    }
                }).then(function () {
                    if (kecVal) {
                        return domisiliCtrl.loadKelurahan(kelVal);
                    }
                });
            } else {
                domisiliCtrl.provinsi.val('').trigger('change.select2');
                domisiliCtrl.loadKabupaten('');
            }
        }

        function handleCheckboxChange() {
            const isChecked = checkbox.is(':checked');
            const domisiliCtrl = groupControllers['domisili'];

            if (isChecked) {
                syncValues();
                if (domisiliCtrl) {
                    domisiliCtrl.lock();
                }
                domisiliGroup.find('.sync-indicator').removeClass('d-none');
            } else {
                if (domisiliCtrl) {
                    domisiliCtrl.unlock();
                }
                domisiliGroup.find('.sync-indicator').addClass('d-none');
            }
        }

        checkbox.on('change', handleCheckboxChange);

        // While checked, listen to asal changes and sync to domisili in real time
        asalTextarea.on('input propertychange', function () {
            if (checkbox.is(':checked')) {
                domisiliTextarea.val(asalTextarea.val());
            }
        });

        const asalCtrl = groupControllers['asal'];
        if (asalCtrl) {
            asalCtrl.provinsi.on('change.syncDomisili', function () {
                if (checkbox.is(':checked')) {
                    syncValues();
                }
            });
            asalCtrl.kabupaten.on('change.syncDomisili', function () {
                if (checkbox.is(':checked')) {
                    syncValues();
                }
            });
            asalCtrl.kecamatan.on('change.syncDomisili', function () {
                if (checkbox.is(':checked')) {
                    syncValues();
                }
            });
            asalCtrl.kelurahan.on('change.syncDomisili', function () {
                if (checkbox.is(':checked')) {
                    const domisiliCtrl = groupControllers['domisili'];
                    if (domisiliCtrl) {
                        domisiliCtrl.kelurahan.val(asalCtrl.kelurahan.val()).trigger('change.select2');
                    }
                }
            });
        }

        // Before submitting form, re-enable disabled elements so form submits their values
        form.on('submit', function () {
            if (checkbox.is(':checked')) {
                syncValues();
                domisiliGroup.find('select, input, textarea').prop('disabled', false);
            }
        });

        // Initialize state on page load
        if (checkbox.is(':checked')) {
            handleCheckboxChange();
        }
    });
})();
