const acc = document.getElementsByClassName('accordion');

for(let i = 0; i < acc.length; i++)
{
  acc[i].addEventListener('click', function ()
  {
    this.classList.toggle('active');
    const panel = this.nextElementSibling;
    panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + 'px';
  });
}
