document.addEventListener('click', function (e)
{
  const target = e.target;
  if(target.classList.contains('accordion'))
  {
    target.classList.toggle('active');
    const panel = this.nextElementSibling;
    panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + 'px';
  }
});
