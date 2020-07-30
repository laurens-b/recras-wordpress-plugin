/**
 * Disable arrangements that are not allowed for this contact form
 *
 * @param {Array} packageIDs List of package IDs
 */
function disableNotAllowed(packageIDs)
{
    let optionEls = document.getElementById('arrangement_id').getElementsByTagName('option');
    for (let optionEl of optionEls) {
        optionEl.disabled = !packageIDs.includes(parseInt(optionEl.value,10));
    }
}

/*
 * Get packages that are valid for a given contact form
 *
 * @param {Number} formID
 * @param {String} subdomain
 */
function getContactFormArrangements(formID, subdomain)
{
    if (!formID) {
        return false;
    }

    let lastResponse;
    fetch(`https://${subdomain}.recras.nl/api2.php/contactformulieren/${formID}/arrangementen`)
        .then(res => {
            lastResponse = res;
            return res.json();
        })
        .then(json => {
            if (!lastResponse.ok) {
                alert(recras_l10n.no_connection);
                return;
            }
            let contactFormPackages = json.map(i => i.arrangement_id);
            disableNotAllowed(contactFormPackages);
        })
        .catch(res => {
            alert(recras_l10n.no_connection);
        });
}
