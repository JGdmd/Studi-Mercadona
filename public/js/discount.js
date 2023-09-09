let select = document.getElementById('discount_product');
let div = document.getElementById('info');
let before = document.getElementById('before');
let after = document.getElementById('after');
let discountInput = document.getElementById('discount_discount');
let form = document.querySelector('form-admin');
div.style.display = "none";
let calendar = document.getElementById('calendar');
let list = document.querySelector('.list');
select.addEventListener('change', async () => {
    let productId = select.value;
    let path = '/get-product/' + productId;
    const response = await fetch(path);

    if (!response.ok) {
        // Gérer les cas d'erreur si nécessaire
    } else {
        const body = await response.text();
        const data = JSON.parse(body);
        let discount = data.price - (data.price * discountInput.value / 100);
        let discountsExist = data.discounts;
        let discountRound = discount.toFixed(2);
        console.log(data);
        calendar.style.display = "block";
        div.style.display = "block";
        before.innerHTML = `${data.price.toFixed(2)}€`;
        after.innerHTML = `${discountRound}€`
        while(list.firstChild) {
            list.removeChild(list.firstChild);
        }
        discountsExist.forEach(entry => {
            console.log(entry);
            let period = [new Date(entry.begins), new Date(entry.ends)]
            let i = 0;
            period.forEach(date => {
                const day = date.getDate().toString().padStart(2, '0');
                const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Notez que les mois sont indexés à partir de 0 (0 = janvier, 1 = février, etc.)
                const year = date.getFullYear();
                const formattedDate = `${day}/${month}/${year}`;
                period[i] = formattedDate;
                i++;
            });
            let li = document.createElement('li');
            li.innerHTML = `Du ${period[0]}, au ${period[1]}`;
            list.appendChild(li);
        });
        discountInput.addEventListener('input', () => {
            discount = data.price - (data.price * discountInput.value / 100);
            discountRound = discount.toFixed(2);
            before.innerHTML = `${data.price.toFixed(2)}€`;
            after.innerHTML = ` ${discountRound}€`
        })
    }
});


