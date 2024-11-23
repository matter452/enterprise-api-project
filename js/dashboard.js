const loan_number = document.getElementById("loan_number");
const loanRadio = document.getElementById("loan_radio");
const documentRadio = document.getElementById("document_radio");
const searchButton = document.getElementById("search_button");
const form = document.getElementById("search");
const docFormType = document.getElementById("document_doc_select");
const startDate = document.getElementById("start_date");
const endDate = document.getElementById("end_date");
const loanSection = document.getElementById("loan_section");
const documentSection = document.getElementById("document_section");


function disableSection() {
    const selected = document.querySelector('input[name="search_by"]:checked');
    let endpoint = '';


    switch (selected.value) {
        case 'loan_radio':
            documentSection.disabled = true;
            loanSection.disabled = false;
            loanSection.classList.remove("opacity-50");
            documentSection.classList.add("opacity-50");
            endpoint = '/api/documents.php';
            form.action = endpoint;
            break;
        case 'document_radio':
            loanSection.disabled = true;
            documentSection.disabled = false;
            documentSection.classList.remove("opacity-50");
            loanSection.classList.add("opacity-50");
            endpoint = '/api/documents.php';
            form.action = endpoint;
            break;
    }
}

function addValidationClasses(element, valid) {
    if (valid) {
        element.classList.remove("border-2", "border-red-600");
        element.classList.add("border-2", "border-green-600");
    } else {
        element.classList.remove("border-2", "border-green-600");
        element.classList.add("border-2", "border-red-600");
    }
}

function validateLoanNumber() {
    const loanNumberRegex = /^\d{5,9}$/;
    const isValid = loanNumberRegex.test(loan_number.value);
    addValidationClasses(loan_number, isValid);
    return isValid;
}

function validateRadioSelection() {
    const selected = document.querySelector('input[name="search_by"]:checked');
    const isValid = !!selected;
    if (!isValid) {
        documentRadio.parentElement.classList.add("border-2", "border-red-600");
        loanRadio.parentElement.classList.add("border-2", "border-red-600");
    } else {
        documentRadio.parentElement.classList.remove("border-2", "border-red-600");
        loanRadio.parentElement.classList.remove("border-2", "border-red-600");
    }
    return isValid;
}

function validateDates() {
    const start = new Date(startDate.value);
    const end = new Date(endDate.value);
    const isValid = startDate.value && endDate.value && start < end;
    addValidationClasses(startDate, isValid);
    addValidationClasses(endDate, isValid);
    return isValid;
}

function validateForm() {
    const isRadioValid = validateRadioSelection();

    let allValid = true;

    if (!loanSection.disabled) {
        const isLoanNumberValid = validateLoanNumber();
        allValid = allValid && isLoanNumberValid;
    }
    if (!documentSection.disabled) {
        const areDatesValid = validateDates();
        allValid = allValid && areDatesValid;
    }
    allValid = allValid && isRadioValid;

    return allValid;
}

async function getSearchResults() {
    const formvalues = new FormData(form);
    let url = new URL(form.action, 'https://www.ec2-34-220-147-199.us-west-2.compute.amazonaws.com');
    let params = new URLSearchParams(formvalues).toString();
    url.search = params;

    try {
        const response = await fetch(url.toString());
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        const json = await response.json();
        sessionStorage.setItem('results', JSON.stringify(json));
        window.location.assign('/results.php');

    } catch (error) {
        console.error(error.message);
    }
}

loanRadio.addEventListener("change", disableSection);
documentRadio.addEventListener("change", disableSection);
loan_number.addEventListener("change", () => validateLoanNumber());
startDate.addEventListener("change", validateDates);
endDate.addEventListener("change", validateDates);
searchButton.addEventListener("click", (event) => {
    if (!validateForm()) {
        alert('Invalid form field(s). Please input valid data before submitting.');
    } else {
        getSearchResults();
    }
});
