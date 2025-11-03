const searchInput = document.querySelector('.search-input');
searchInput.addEventListener('keyup', filter);

function filter(e){
    const filter = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.data-item');

    items.forEach((item) => {
        textItem = item.querySelector('h1').textContent.toLowerCase();
        if(textItem.indexOf(filter) !== -1){
            item.style.display = "table-row";
        } else {
            item.style.display = "none";
        }
    })
}