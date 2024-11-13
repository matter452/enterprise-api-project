function validateFile() {
    const fileInput = document.getElementById('fileupload');
    const filePath = fileInput.value;
    const allowedFileExp = /(\.pdf)$/i;
  
    if (!allowedFileExp.exec(filePath)) {
      alert('Please upload a PDF file only.');
      fileInput.classList.add('border', 'border-danger');
      fileInput.value = '';
    }
    else{
        fileInput.className = '';
    }
  }
  
  function removeFile()
  {
    const fileInput = document.getElementById('fileupload');
    fileInput.value = '';
  }

  function validateLoanNum(event){
    let numInput = event.target.value;
    const regex = /^[0-9]{5,9}$/;
    numInput = input.value.replace(/[^0-9]/g, '');
    const match = regex.test(numInput);

    if(!match)
    {
      event.target.classList.add('border', 'border-danger');
      document.getElementById('loanNumMessage').className ='text-danger';
    }else
    {
      document.getElementById('loanNumMessage').className ='d-none text-danger';
    }
  }