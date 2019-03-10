var $ = require('jquery');

function count() {
  var $jsCountArea = $('.js-count-target');
  var $jsCountShow = $('.js-count-show');

  $($jsCountArea).on('keyup', function () {
    var count = $($jsCountArea).val().length;
    $($jsCountShow).text(count);
  });
}

module.exports = count;
