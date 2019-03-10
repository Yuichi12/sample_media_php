var $ = require('jquery');

// フッターを最下部に固定
function fixFooter() {
  var $ftr = $('.js-fix-footer');
  if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
    $ftr.attr({
      'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
    });
  }
}

module.exports = fixFooter;
