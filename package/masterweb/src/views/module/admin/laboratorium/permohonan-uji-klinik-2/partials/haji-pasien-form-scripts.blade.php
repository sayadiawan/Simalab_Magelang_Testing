(function() {
  var HAJI_PASIEN_BASE_RM = {{ (int) ($count_pasien ?? 1) }};
  var HAJI_PASIEN_CSRF = '{{ csrf_token() }}';
  var HAJI_PASIEN_ROUTES = {
    provinsi: @json(route('get-provinsi')),
    kabupaten: @json(route('get-kabupaten')),
    kecamatan: @json(route('get-kecamatan')),
    desa: @json(route('get-desa')),
    searchWilayah: @json(route('search-wilayah')),
    wilayahDetail: @json(route('get-wilayah-detail'))
  };

  var pasienRowIndex = 0;
  var pasienRowTemplate = {!! json_encode(view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.haji-new-pasien-form', [
    'klinikNumberSettings' => $klinikNumberSettings ?? \Smt\Masterweb\Models\KlinikNumberSettings::getSettings(),
  ])->render()) !!};

  function padRm(num) {
    return String(num).padStart(4, '0');
  }

  function calculateAgeFromDate(birthDate) {
    var today = new Date();
    var years = today.getFullYear() - birthDate.getFullYear();
    var months = today.getMonth() - birthDate.getMonth();
    var days = today.getDate() - birthDate.getDate();
    if (days < 0) {
      months--;
      days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
    }
    if (months < 0) {
      years--;
      months += 12;
    }
    return { years: years, months: months, days: days };
  }

  function parseBirthDateStr(dateStr) {
    if (!dateStr || dateStr.indexOf('/') === -1) return null;
    var parts = dateStr.split('/');
    if (parts.length !== 3) return null;
    var day = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10) - 1;
    var year = parseInt(parts[2], 10);
    var d = new Date(year, month, day);
    return isNaN(d.getTime()) ? null : d;
  }

  function updateAgeDisplay($row, dateStr) {
    var $container = $row.find('.js-age-display-container');
    if (!dateStr) {
      $container.hide();
      return;
    }
    var birth = parseBirthDateStr(dateStr);
    if (!birth) {
      $container.hide();
      return;
    }
    var age = calculateAgeFromDate(birth);
    $row.find('.js-age-years').text(age.years);
    $row.find('.js-age-months').text(age.months);
    $row.find('.js-age-days').text(age.days);
    $container.show();
  }

  function setBirthDate($row, formatted) {
    $row.find('.js-tgllahir-hidden').val(formatted);
    updateAgeDisplay($row, formatted);
  }

  function updateBirthFromDropdown($row) {
    var day = $row.find('.js-birth-day').val();
    var month = $row.find('.js-birth-month').val();
    var year = $row.find('.js-birth-year').val();
    var $display = $row.find('.js-selected-birth-date');

    if (day && month && year) {
      var formatted = day + '/' + month + '/' + year;
      var monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      $display.html('<i class="fa fa-check-circle mr-2" style="color: #4caf50;"></i>' + day + ' ' + monthNames[parseInt(month, 10)] + ' ' + year);
      setBirthDate($row, formatted);
    } else {
      $display.text('-- Belum dipilih --');
      setBirthDate($row, '');
    }
  }

  function formatManualBirthInput(input) {
    var value = input.value.replace(/[^\d]/g, '');
    var formatted = '';
    if (value.length > 0) formatted = value.substring(0, 2);
    if (value.length >= 2) formatted += '/';
    if (value.length >= 3) formatted += value.substring(2, 4);
    if (value.length >= 4) formatted += '/';
    if (value.length >= 5) formatted += value.substring(4, 8);
    input.value = formatted;
    return formatted;
  }

  function initBirthDateRow($row) {
    var $day = $row.find('.js-birth-day');
    var $year = $row.find('.js-birth-year');
    var currentYear = new Date().getFullYear();

    $day.find('option:not(:first)').remove();
    for (var i = 1; i <= 31; i++) {
      var d = String(i).padStart(2, '0');
      $day.append('<option value="' + d + '">' + d + '</option>');
    }

    $year.find('option:not(:first)').remove();
    for (var y = currentYear; y >= currentYear - 100; y--) {
      $year.append('<option value="' + y + '">' + y + '</option>');
    }

    $row.find('.js-birth-day, .js-birth-month, .js-birth-year').off('change.hajiBirth').on('change.hajiBirth', function() {
      updateBirthFromDropdown($row);
    });

    $row.find('.js-btn-birth-dropdown').off('click.hajiBirth').on('click.hajiBirth', function(e) {
      e.preventDefault();
      $row.find('.js-btn-birth-dropdown').removeClass('btn-outline-primary').addClass('btn-primary');
      $row.find('.js-btn-birth-manual').removeClass('btn-primary').addClass('btn-outline-primary');
      $row.find('.js-birth-dropdown-container').show();
      $row.find('.js-birth-manual-container').hide();
      $row.find('.js-birth-manual-input').val('');
    });

    $row.find('.js-btn-birth-manual').off('click.hajiBirth').on('click.hajiBirth', function(e) {
      e.preventDefault();
      $row.find('.js-btn-birth-manual').removeClass('btn-outline-primary').addClass('btn-primary');
      $row.find('.js-btn-birth-dropdown').removeClass('btn-primary').addClass('btn-outline-primary');
      $row.find('.js-birth-dropdown-container').hide();
      $row.find('.js-birth-manual-container').show();
      $row.find('.js-birth-day, .js-birth-month, .js-birth-year').val('');
      $row.find('.js-selected-birth-date').text('-- Belum dipilih --');
    });

    $row.find('.js-birth-manual-input').off('input.hajiBirth').on('input.hajiBirth', function() {
      var formatted = formatManualBirthInput(this);
      if (formatted.length === 10) {
        var parts = formatted.split('/');
        var day = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var year = parseInt(parts[2], 10);
        if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= currentYear) {
          setBirthDate($row, formatted);
        } else {
          setBirthDate($row, '');
        }
      } else {
        setBirthDate($row, '');
      }
    });
  }

  function initGenderRow($row) {
    $row.find('.js-gender-card').off('click.hajiGender').on('click.hajiGender', function() {
      $(this).find('.js-gender-radio').prop('checked', true).trigger('change');
    });
    $row.find('.js-gender-radio').off('change.hajiGender').on('change.hajiGender', function() {
      $row.find('.js-gender-card').removeClass('border-primary').css('border-width', '0');
      $(this).closest('.js-gender-card').addClass('border-primary').css('border-width', '3px');
    });
    $row.find('.js-gender-radio:checked').closest('.js-gender-card').addClass('border-primary').css('border-width', '3px');
  }

  function initInputValidation($row) {
    $row.find('.js-nik-pasien').off('input.hajiVal').on('input.hajiVal', function() {
      this.value = this.value.replace(/[^\d]/g, '');
    });
    $row.find('.js-phone-pasien').off('input.hajiVal').on('input.hajiVal', function() {
      this.value = this.value.replace(/[^\d]/g, '');
    });
    $row.find('.js-nama-pasien').off('input.hajiVal').on('input.hajiVal', function() {
      this.value = this.value.toUpperCase();
    });
  }

  function loadProvinsiForRow($row) {
    var $prov = $row.find('.js-provinsi-pasien');
    if ($prov.data('loaded')) return;
    $.get(HAJI_PASIEN_ROUTES.provinsi, function(response) {
      $prov.html('<option value="">-- Pilih Provinsi --</option>');
      $.each(response, function(_, item) {
        $prov.append('<option value="' + item.id_wilayah + '" data-kode="' + item.wilayah_kode + '">' + item.wilayah + '</option>');
      });
      $prov.data('loaded', true);
    });
  }

  function initWilayahRow($row) {
    loadProvinsiForRow($row);

    $row.find('.js-btn-toggle-manual-wilayah').off('click.hajiWil').on('click.hajiWil', function() {
      $row.find('.js-manual-wilayah-selector').slideToggle(300);
    });
    $row.find('.js-btn-hide-manual-wilayah').off('click.hajiWil').on('click.hajiWil', function() {
      $row.find('.js-manual-wilayah-selector').slideUp(300);
    });

    $row.find('.js-provinsi-pasien').off('change.hajiWil').on('change.hajiWil', function() {
      var kode = $(this).find(':selected').data('kode');
      var id = $(this).val();
      var $kab = $row.find('.js-kabupaten-pasien');
      var $kec = $row.find('.js-kecamatan-pasien');
      var $desa = $row.find('.js-desa-pasien');
      $kec.html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
      $desa.html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled', true);
      if (!id) {
        $kab.html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop('disabled', true);
        return;
      }
      $.post(HAJI_PASIEN_ROUTES.kabupaten, { _token: HAJI_PASIEN_CSRF, provinsi_kode: kode }, function(response) {
        $kab.html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop('disabled', false);
        $.each(response, function(_, item) {
          $kab.append('<option value="' + item.id_wilayah + '" data-kode="' + item.wilayah_kode + '">' + item.wilayah + '</option>');
        });
      });
    });

    $row.find('.js-kabupaten-pasien').off('change.hajiWil').on('change.hajiWil', function() {
      var kode = $(this).find(':selected').data('kode');
      var id = $(this).val();
      var $kec = $row.find('.js-kecamatan-pasien');
      var $desa = $row.find('.js-desa-pasien');
      $desa.html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled', true);
      if (!id) {
        $kec.html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
        syncAlamatFromWilayah($row);
        return;
      }
      $.post(HAJI_PASIEN_ROUTES.kecamatan, { _token: HAJI_PASIEN_CSRF, kabupaten_kode: kode }, function(response) {
        $kec.html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', false);
        $.each(response, function(_, item) {
          $kec.append('<option value="' + item.id_wilayah + '" data-kode="' + item.wilayah_kode + '">' + item.wilayah + '</option>');
        });
      });
      syncAlamatFromWilayah($row);
    });

    $row.find('.js-kecamatan-pasien').off('change.hajiWil').on('change.hajiWil', function() {
      var kode = $(this).find(':selected').data('kode');
      var id = $(this).val();
      var $desa = $row.find('.js-desa-pasien');
      if (!id) {
        $desa.html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled', true);
        syncAlamatFromWilayah($row);
        return;
      }
      $.post(HAJI_PASIEN_ROUTES.desa, { _token: HAJI_PASIEN_CSRF, kecamatan_kode: kode }, function(response) {
        $desa.html('<option value="">-- Pilih Desa/Kelurahan --</option>').prop('disabled', false);
        $.each(response, function(_, item) {
          $desa.append('<option value="' + item.id_wilayah + '" data-kode="' + item.wilayah_kode + '">' + item.wilayah + '</option>');
        });
      });
      syncAlamatFromWilayah($row);
    });

    $row.find('.js-desa-pasien').off('change.hajiWil').on('change.hajiWil', function() {
      syncAlamatFromWilayah($row);
    });

    var searchTimer;
    $row.find('.js-search-wilayah').off('input.hajiWil').on('input.hajiWil', function() {
      var keyword = $(this).val().trim();
      var $results = $row.find('.js-search-wilayah-results');
      var $list = $row.find('.js-search-wilayah-results-list');
      clearTimeout(searchTimer);
      if (keyword.length < 2) {
        $results.hide();
        return;
      }
      searchTimer = setTimeout(function() {
        $.get(HAJI_PASIEN_ROUTES.searchWilayah, { keyword: keyword, limit: 10 }, function(response) {
          $list.empty();
          if (!response.length) {
            $list.html('<div class="p-3 text-center text-muted">Wilayah tidak ditemukan</div>');
          } else {
            $.each(response, function(_, item) {
              $list.append(
                '<a href="javascript:void(0)" class="list-group-item list-group-item-action js-wilayah-result-item"' +
                ' data-id="' + item.id + '">' + item.nama +
                '<div class="small text-muted">' + (item.full_path || '') + '</div></a>'
              );
            });
          }
          $results.show();
        });
      }, 400);
    });

    $row.off('click.hajiWilResult').on('click.hajiWilResult', '.js-wilayah-result-item', function() {
      var wilayahId = $(this).data('id');
      $row.find('.js-search-wilayah-results').hide();
      $row.find('.js-search-wilayah').val('');
      $row.find('.js-manual-wilayah-selector').show();
      $.get(HAJI_PASIEN_ROUTES.wilayahDetail, { wilayah_id: wilayahId }, function(response) {
        var parents = response.parents || {};
        if (parents.provinsi_id) {
          $row.find('.js-provinsi-pasien').val(parents.provinsi_id).trigger('change');
          setTimeout(function() {
            if (parents.kabupaten_id) {
              $row.find('.js-kabupaten-pasien').val(parents.kabupaten_id).trigger('change');
              setTimeout(function() {
                if (parents.kecamatan_id) {
                  $row.find('.js-kecamatan-pasien').val(parents.kecamatan_id).trigger('change');
                  setTimeout(function() {
                    if (parents.desa_id) {
                      $row.find('.js-desa-pasien').val(parents.desa_id);
                    }
                  }, 500);
                }
              }, 500);
            }
          }, 500);
        }
        if (response.full_path) {
          $row.find('.js-alamat-pasien').val(response.full_path);
        }
      });
    });
  }

  function getWilayahPartsFromRow($row) {
    var parts = [];
    var desaText = $row.find('.js-desa-pasien option:selected').text();
    var kecText = $row.find('.js-kecamatan-pasien option:selected').text();
    var kabText = $row.find('.js-kabupaten-pasien option:selected').text();
    var provText = $row.find('.js-provinsi-pasien option:selected').text();

    if (desaText && desaText !== '-- Pilih Desa/Kelurahan --') parts.push(desaText);
    if (kecText && kecText !== '-- Pilih Kecamatan --') parts.push(kecText);
    if (kabText && kabText !== '-- Pilih Kabupaten/Kota --') parts.push(kabText);
    if (provText && provText !== '-- Pilih Provinsi --') parts.push(provText);

    return parts;
  }

  function syncAlamatFromWilayah($row) {
    var fullWilayah = getWilayahPartsFromRow($row).join(', ');
    if (fullWilayah) {
      $row.find('.js-alamat-pasien').val(fullWilayah);
    }
  }

  function initHajiPasienRow($row) {
    initBirthDateRow($row);
    initGenderRow($row);
    initInputValidation($row);
    initWilayahRow($row);
  }

  function renumberPasienRows() {
    $('#pasien-list .pasien-row').each(function(idx) {
      var newIndex = idx + 1;
      var $row = $(this);
      $row.attr('data-index', newIndex);
      $row.find('.card-header strong').text('Pasien #' + newIndex);
      $row.find('.btn-remove-pasien').attr('data-index', newIndex);
      $row.find('[name^="pasien["]').each(function() {
        var name = $(this).attr('name');
        if (name) {
          $(this).attr('name', name.replace(/pasien\[\d+\]/, 'pasien[' + newIndex + ']'));
        }
      });
    });
    pasienRowIndex = $('#pasien-list .pasien-row').length;
  }

  window.resetPasienRows = function() {
    pasienRowIndex = 0;
  };

  window.addPasienRow = function() {
    pasienRowIndex++;
    var noRm = padRm(HAJI_PASIEN_BASE_RM + pasienRowIndex - 1);
    var html = pasienRowTemplate.replace(/__IDX__/g, pasienRowIndex).replace(/__NO_RM__/g, noRm);
    var $row = $(html);
    $('#pasien-list').append($row);
    initHajiPasienRow($row);
  };

  $(document).on('input', '.js-nomor-lab-manual, .js-nomor-sample-manual', function() {
    $(this).val(String($(this).val() || '').replace(/\D/g, ''));
  });

  $(document).on('click', '#btn-tambah-pasien', function() {
    addPasienRow();
  });

  $(document).on('click', '.btn-remove-pasien', function() {
    var index = $(this).data('index');
    $('.pasien-row[data-index="' + index + '"]').remove();
    if ($('#pasien-list .pasien-row').length === 0) {
      pasienRowIndex = 0;
    } else {
      renumberPasienRows();
    }
  });

  $(document).ready(function() {
    if ($('#pasien_new_group').length && $('input[name="pasien_mode"]:checked').val() === 'new') {
      if ($('#pasien-list .pasien-row').length === 0) {
        addPasienRow();
      }
    }
  });
})();
