var recrasPlugin = function(editor, url) {
    editor.addButton('recras-arrangement', {
        title: recras_l10n.package,
        image: url + '/package.svg',
        onclick: function() {
            tb_show(recras_l10n.package, 'admin.php?page=form-arrangement');
        }
    });

    editor.addButton('recras-availability', {
        title: recras_l10n.package_availability,
        image: url + '/availability.svg',
        onclick: function() {
            tb_show(recras_l10n.package_availability, 'admin.php?page=form-package-availability');
        }
    });

    editor.addButton('recras-booking', {
        title: recras_l10n.online_booking,
        image: url + '/online-booking.svg',
        onclick: function() {
            tb_show(recras_l10n.online_booking, 'admin.php?page=form-booking');
        }
    });

    editor.addButton('recras-bookprocess', {
        title: recras_l10n.bookprocess,
        image: url + '/bookprocess.svg',
        onclick: function() {
            tb_show(recras_l10n.bookprocess, 'admin.php?page=form-bookprocess');
        }
    });

    editor.addButton('recras-contact', {
        title: recras_l10n.contact_form,
        image: url + '/contact.svg',
        onclick: function() {
            tb_show(recras_l10n.contact_form, 'admin.php?page=form-contact');
        }
    });

    editor.addButton('recras-product', {
        title: recras_l10n.product,
        image: url + '/product.svg',
        onclick: function() {
            tb_show(recras_l10n.product, 'admin.php?page=form-product');
        }
    });

    editor.addButton('recras-voucher-sales', {
        title: recras_l10n.voucherSales,
        image: url + '/vouchers.svg',
        onclick: function() {
            tb_show(recras_l10n.product, 'admin.php?page=form-voucher-sales');
        }
    });

    editor.addButton('recras-voucher-info', {
        title: recras_l10n.voucherInfo,
        image: url + '/vouchers.svg',
        onclick: function() {
            tb_show(recras_l10n.product, 'admin.php?page=form-voucher-info');
        }
    });
};

tinymce.PluginManager.add('recras', recrasPlugin);
