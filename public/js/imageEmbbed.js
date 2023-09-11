  let fileReader = document.getElementById('product_image');
  let output = document.getElementById('output');

  fileReader.addEventListener('change', (e) => {
    let reader = new FileReader();
    reader.onload = function(){
        output.style.display = 'Block';
      output.src = reader.result;
    };
    reader.readAsDataURL(e.target.files[0]);
  });