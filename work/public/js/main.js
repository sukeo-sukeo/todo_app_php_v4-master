'use strict';

{
  const checkboxes = document.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach(box => {
    box.addEventListener('change', () => {
      box.parentNode.submit();
    })
  })

  const deletes = document.querySelectorAll('.delete');
  deletes.forEach(del => {
    del.addEventListener('click', () => {
      if (!confirm('Are you sure?')) {
        return;
      }
      del.parentNode.submit();
    })
  })
}