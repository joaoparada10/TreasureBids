function formatDateTimeLocal(date) {
    const pad = (num) => String(num).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const now = new Date();
const startingDate = new Date(now.getTime() + 5 * 60 * 1000); // Now + 5 minutes
const endDate = new Date(startingDate.getTime() + 48 * 60 * 60 * 1000); // Starting Date + 48 hours

document.getElementById('starting_date').value = formatDateTimeLocal(startingDate);
document.getElementById('end_date').value = formatDateTimeLocal(endDate);

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('auction-form');

    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });

    const checkboxes = document.querySelectorAll('.category-checkbox');
    const categoryError = document.getElementById('category-error');

    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', () => {
        const checked = document.querySelectorAll('.category-checkbox:checked');
        if (checked.length > 2) {
          categoryError.style.display = 'block';
          checkbox.checked = false;
        } else {
          categoryError.style.display = 'none';
        }
      });
    });

    const pictureInput = document.getElementById('picture');
    const previewImg = document.getElementById('preview-img');

    pictureInput.addEventListener('change', function (event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewImg.src = e.target.result;
          previewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        previewImg.style.display = 'none';
      }
    });
  });


