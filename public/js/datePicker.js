  let datePickers = document.querySelectorAll('.js-date');
  let begins = datePickers[0];
  let ends = datePickers[1];

datePickers.forEach(datePicker => {
    datePicker.addEventListener('focus', () => {
        datePicker.blur();
    })
});

  const beginsPicker = new Litepicker({ 
    element: begins,
    lang: "fr-FR",
    minDate: Date(),
    format: 'YYYY-MM-DD',
    scrollToDate: true,
    autoRefresh: true,
    plugins: ['mobilefriendly']
  });
  const endsPicker = new Litepicker({ 
    element: ends,
    lang: "fr-FR",
    minDate: Date(),
    format: 'YYYY-MM-DD',
    scrollToDate: true,
    autoRefresh: true,
    plugins: ['mobilefriendly']
  });