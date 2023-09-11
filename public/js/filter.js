let select = document.getElementById('filter');
let catalog = document.querySelector('.catalog');
let navigationPage = document.querySelector('.nav-page');
let next = document.getElementById('next');
let previous = document.getElementById('previous');

select.addEventListener('change', async () => {
    let categoryId = select.value;
    let path = '/get-products-by-category-ajax/' + categoryId;
    const response = await fetch(path);

    if(next) {
        next.remove();
    }
    if(previous) {
        previous.remove();
    }

    if (!response.ok) {
        console.log('Une erreur est survenue. Réessayez');
    } else {
        const body = await response.text();
        const data = JSON.parse(body);
        let products = data.products;
        if(data.succes === true) {
            while(catalog.firstChild) {
                catalog.removeChild(catalog.firstChild);
            }
            products.forEach(product => {
                console.log(product);
                let div = document.createElement('div');
                div.classList.add('product');
                let image = document.createElement('img');
                image.src = '../' + product.image;
                div.appendChild(image);
                let label = document.createElement('span');
                label.classList.add('product-label');
                label.textContent = product.label;
                div.appendChild(label);
                let description = document.createElement('span');
                description.classList.add('product-description');
                description.textContent = product.description;
                div.appendChild(description);
                let divPrice = document.createElement('div');
                if(product['discount'] == null) {
                    let price = document.createElement('span');
                    price.classList.add('product-price');
                    price.classList.add('f-green');
                    price.textContent = product.price + ' € / ' + product.unit;
                    divPrice.appendChild(price);
                } else {
                    let price = document.createElement('span');
                    price.classList.add('product-price');
                    price.classList.add('f-green');
                    price.classList.add('old-price');
                    price.textContent = product.price + ' €';
                    divPrice.appendChild(price);
                    let newPrice = document.createElement('span');
                    newPrice.classList.add('product-price');
                    newPrice.classList.add('f-red');
                    newPrice.textContent = product.discount + ' € / ' + product.unit;
                    divPrice.appendChild(newPrice);
                }
                div.appendChild(divPrice);
                catalog.appendChild(div);
            });
            if(data['nextPage']) {
                next.href = '/get-products-by-category/' + categoryId + '/2';
                navigationPage.appendChild(next);
                console.log(next);
            }
        } else {
            while(catalog.firstChild) {
                catalog.removeChild(catalog.firstChild);
            }
            let div = document.createElement('div');
            div.id = 'notFound';
            let alert = document.createElement('h2');
            alert.style.textAlign = "center";
            alert.textContent = 'Pas de produits pour cette catégorie';
            let image = document.createElement('img');
            image.src = '../assets/notFound.png';
            div.appendChild(alert);
            div.appendChild(image);
            catalog.appendChild(div);
        }
    }
});


