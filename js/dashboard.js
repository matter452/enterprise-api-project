const loanRadio = document.getElementById("loan_radio");
const documentRadio = document.getElementById("document_radio");
const searchButton = document.getElementById("search_button");
const form = document.getElementById("search");
const docFormType = document.getElementById("document_doc_select");
const startDate = document.getElementById("start_date");
const endDate = document.getElementById("end_date");
loanRadio.addEventListener("change", disableSection);
documentRadio.addEventListener("change", disableSection);


function disableSection()
{
    let endpoint = '';
    const selected = document.querySelector('input[name="search_by"]:checked');
    const loanSection = document.getElementById("loan_section");
    const documentSection  = document.getElementById("document_section");
    

    switch(selected.value){
        case 'loan_radio':
            documentSection.disabled = true;
            loanSection.disabled = false;
            endpoint = '/api/documents.php';
            form.action = endpoint;
            break;
        case 'document_radio':
            loanSection.disabled = true;
            documentSection.disabled = false;
            endpoint = '/api/documents.php';
            form.action = endpoint;
            break;
    }
}

async function getSearchResults()
{
    const formvalues = new FormData(form);
    let url = new URL(form.action,'https://www.ec2-34-220-147-199.us-west-2.compute.amazonaws.com');
    let params = new URLSearchParams(formvalues).toString();
    url.search = params;
    
    try{
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