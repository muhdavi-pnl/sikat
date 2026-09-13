/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

var confirmationVariants = {
  default: {
	icon: 'question',
	confirmButtonColor: '#6777ef',
	cancelButtonColor: '#6c757d',
	confirmButtonText: 'Ya',
	cancelButtonText: 'Tidak',
	defaultTitle: 'Apakah Anda yakin?',
	defaultText: 'Silakan konfirmasi untuk melanjutkan tindakan ini.',
	processingToastText: 'Permintaan Anda sedang diproses...'
  },
  delete: {
	icon: 'warning',
	confirmButtonColor: '#dc3545',
	cancelButtonColor: '#6c757d',
	confirmButtonText: 'Ya, hapus',
	cancelButtonText: 'Tidak',
	defaultTitle: 'Yakin ingin menghapus data ini?',
	defaultText: 'Data yang dipilih akan dihapus dari sistem.',
	processingToastText: 'Menghapus data...'
  },
  'force-delete': {
	icon: 'error',
	confirmButtonColor: '#dc3545',
	cancelButtonColor: '#6c757d',
	confirmButtonText: 'Ya, hapus permanen',
	cancelButtonText: 'Tidak',
	defaultTitle: 'Yakin ingin menghapus permanen data ini?',
	defaultText: 'Data yang dipilih akan dihapus permanen dan tidak dapat dipulihkan lagi.',
	processingToastText: 'Menghapus data permanen...'
  },
  reset: {
	icon: 'warning',
	confirmButtonColor: '#f0ad4e',
	cancelButtonColor: '#6c757d',
	confirmButtonText: 'Ya, reset',
	cancelButtonText: 'Tidak',
	defaultTitle: 'Yakin ingin mereset data ini?',
	defaultText: 'Tindakan ini akan mereset data sesuai pengaturan sistem.',
	processingToastText: 'Mereset data...'
  },
  deactivate: {
	icon: 'warning',
	confirmButtonColor: '#fd7e14',
	cancelButtonColor: '#6c757d',
	confirmButtonText: 'Ya, nonaktifkan',
	cancelButtonText: 'Tidak',
	defaultTitle: 'Yakin ingin menonaktifkan data ini?',
	defaultText: 'Data yang dipilih akan dinonaktifkan dari sistem.',
	processingToastText: 'Menonaktifkan data...'
  }
};

function escapeHtml(value) {
  return String(value || '')
	.replace(/&/g, '&amp;')
	.replace(/</g, '&lt;')
	.replace(/>/g, '&gt;')
	.replace(/"/g, '&quot;')
	.replace(/'/g, '&#039;');
}

function buildConfirmationOptions(target) {
  var variant = target.dataset.confirmVariant || 'default';
  var variantConfig = confirmationVariants[variant] || confirmationVariants.default;
  var title = target.dataset.confirmTitle || variantConfig.defaultTitle || 'Apakah Anda yakin?';
  var text = target.dataset.confirmText || variantConfig.defaultText || 'Tindakan ini tidak dapat dibatalkan.';
  var icon = target.dataset.confirmIcon || variantConfig.icon;
  var confirmButtonText = target.dataset.confirmButton || variantConfig.confirmButtonText || 'Ya';
  var cancelButtonText = target.dataset.cancelButton || variantConfig.cancelButtonText || 'Tidak';
  var confirmButtonColor = target.dataset.confirmButtonColor || variantConfig.confirmButtonColor;
  var cancelButtonColor = target.dataset.cancelButtonColor || variantConfig.cancelButtonColor;
  var itemName = target.dataset.confirmItemName || '';
  var itemLabel = target.dataset.confirmItemLabel || 'Data';
  var processingToastText = target.dataset.confirmProcessingToast || variantConfig.processingToastText || 'Permintaan Anda sedang diproses...';
  var html = target.dataset.confirmHtml || '<div>' + escapeHtml(text) + '</div>';

  if (itemName !== '') {
	html += '<div class="confirmation-item-preview">'
	  + '<div class="confirmation-item-label">' + escapeHtml(itemLabel) + '</div>'
	  + '<div class="confirmation-item-name">' + escapeHtml(itemName) + '</div>'
	  + '</div>';
  }

  return {
	title: title,
	text: text,
	html: html,
	icon: icon,
	confirmButtonText: confirmButtonText,
	cancelButtonText: cancelButtonText,
	confirmButtonColor: confirmButtonColor,
	cancelButtonColor: cancelButtonColor,
	itemName: itemName,
	itemLabel: itemLabel,
	processingToastText: processingToastText
  };
}

function fallbackConfirmationMessage(options) {
  var message = options.title + '\n\n' + options.text;

  if (options.itemName !== '') {
	message += '\n\n' + options.itemLabel + ': ' + options.itemName;
  }

  return message;
}

function showProcessingToast(options, onComplete) {
  if (typeof Swal === 'undefined') {
	onComplete();
	return;
  }

  Swal.mixin({
	toast: true,
	position: 'top-end',
	showConfirmButton: false,
	timer: 900,
	timerProgressBar: true,
  }).fire({
	icon: 'success',
	title: options.processingToastText,
  });

  window.setTimeout(onComplete, 150);
}

function showConfirmation(target, onConfirm) {
  var options = buildConfirmationOptions(target);

  if (typeof Swal === 'undefined') {
	if (window.confirm(fallbackConfirmationMessage(options))) {
	  onConfirm();
	}

	return;
  }

  Swal.fire({
	title: options.title,
	html: options.html,
	icon: options.icon,
	showCancelButton: true,
	confirmButtonColor: options.confirmButtonColor,
	cancelButtonColor: options.cancelButtonColor,
	confirmButtonText: options.confirmButtonText,
	cancelButtonText: options.cancelButtonText,
	reverseButtons: true,
	focusCancel: true,
  }).then(function (result) {
	if (result.isConfirmed) {
	  showProcessingToast(options, onConfirm);
	}
  });
}

document.addEventListener('submit', function (event) {
  var form = event.target;

  if (!(form instanceof HTMLFormElement) || !form.classList.contains('js-confirm-submit')) {
	return;
  }

  if (form.dataset.confirmed === 'true') {
	form.dataset.confirmed = 'false';
	return;
  }

  event.preventDefault();

  var submitConfirmed = function () {
	form.dataset.confirmed = 'true';
	form.submit();
  };

  showConfirmation(form, submitConfirmed);
});

document.addEventListener('click', function (event) {
  var link = event.target.closest('.js-confirm-link');

  if (!(link instanceof HTMLAnchorElement)) {
  return;
  }

  if (link.classList.contains('disabled') || link.getAttribute('aria-disabled') === 'true') {
  event.preventDefault();
  return;
  }

  event.preventDefault();

  showConfirmation(link, function () {
  window.location.href = link.href;
  });
});

