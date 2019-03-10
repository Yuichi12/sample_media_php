var $ = require('jquery');

function modal() {
  $('.js-modal-trigger').on('click', function () {
    $('.js-modal-target').show();
  });
}

module.exports = modal;
