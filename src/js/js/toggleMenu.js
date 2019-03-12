var $ = require('jquery');

function toggleMenu() {
  $(function(){

    var $trigger = $('.js-toggle-sp-menu');
    var $menuLink = $('.js-menu-link');
    var $toggleBg = $('.js-toggle-bg');
    var $toggleTarget = $('.js-toggle-target');

    $trigger.on('click', function(){
      $trigger.toggleClass('active');
      $toggleBg.toggleClass('active');
      $toggleTarget.toggleClass('active');
    });
    $menuLink.on('click', function(){
      $trigger.toggleClass('active');
      $toggleBg.toggleClass('active');
      $toggleTarget.toggleClass('active');
    });
  });
};

module.exports = toggleMenu;