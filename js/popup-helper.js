// Popup helper extracted from index.html
function closePopup(popupId, checkboxId, storageKey) {
  var checkbox = document.getElementById(checkboxId);
  if (checkbox && checkbox.checked) {
    localStorage.setItem(storageKey, 'true');
  }
  var popup = document.getElementById(popupId);
  if (popup) {
    popup.style.display = 'none';
  }
}
