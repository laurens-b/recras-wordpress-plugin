function removeElsWithClass(className)
{
    var els = document.querySelectorAll('.' + className);
    for (var i = 0; i < els.length; i++) {
        els[i].parentNode.removeChild(els[i]);
    }
}

function submitRecrasForm(formID, subdomain, basePath, redirect)
{
    removeElsWithClass('recras-error');

    var formEl = document.getElementById('recras-form' + formID);
    var formElements = formEl.querySelectorAll('input, textarea, select');
    var elements = {};
    for (var i = 0; i < formElements.length; i++) {
        if (formElements[i].type !== 'submit') {
            if (formElements[i].value === '' && formElements[i].required === false) {
                formElements[i].value = null;
            }
            if (formElements[i].type === 'checkbox') {
                elements[formElements[i].name] = [];
                var checked = document.querySelectorAll('input[name="' + formElements[i].name + '"]:checked');
                for (var j = 0; j < checked.length; j++) {
                    elements[formElements[i].name].push(checked[j].value);
                }
            } else {
                elements[formElements[i].name] = formElements[i].value;
            }
        }
    }

    formEl.querySelector('[type="submit"]').parentNode.insertAdjacentHTML('beforeend', '<img src="' + basePath + 'editor/loading.gif" alt="' + recras_l10n.loading + '" class="recras-loading">');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'https://' + subdomain + '.recras.nl/api2.php/contactformulieren/' + formID + '/opslaan');
    xhr.send(JSON.stringify(elements));
    xhr.onreadystatechange = function(){
        if (xhr.readyState === 4) {
            removeElsWithClass('recras-loading');
            var response = JSON.parse(xhr.response);
            if (response.success) {
                if (redirect) {
                    window.location = redirect;
                } else {
                    formEl.querySelector('[type="submit"]').parentNode.insertAdjacentHTML('beforeend', '<p class="recras-success">' + recras_l10n.sent_success + '</p>');
                }
            } else if (response.error) {
                var errors = response.error.messages;
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        formEl.querySelector('[name="' + key + '"]').parentNode.insertAdjacentHTML('beforeend', '<span class="recras-error">' + errors[key] + '</span>');
                    }
                }
                formEl.querySelector('[type="submit"]').parentNode.insertAdjacentHTML('beforeend', '<p class="recras-error">' + recras_l10n.sent_error + '</p>');
            } else {
                console.log('Unknown response: ', response);
            }
        }
    };
    return false;
}
