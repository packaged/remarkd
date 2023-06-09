document.addEventListener('click', function (e) {
  var tab = e.target.getAttribute('data-tab-focus-key');
  if(tab)
  {
    setActiveTab(tab);
    e.preventDefault();
  }
});

let url = new URL(window.location.href);
let tabKey = url.searchParams.get("tab");
if(tabKey)
{
  setActiveTab(tabKey);
}

function setActiveTab(tabKey)
{
  let tabs = document.querySelectorAll('.tabs .tab[data-tab-key=' + tabKey + ']');
  tabs.forEach(function (tab) {
    let tabGroup = tab.parentNode.parentNode;
    tabGroup.querySelectorAll('.tab-header li a').forEach(function (tabHeader) {
      if(tabHeader.getAttribute('data-tab-focus-key') === tabKey)
      {
        tabHeader.classList.add('active');
      }
      else
      {
        tabHeader.classList.remove('active');
      }
    });
    tab.parentNode.querySelectorAll('.tab').forEach(function (otab) {
      otab.style.display = 'none';
    });
    tab.style.display = 'block';
  });
}
