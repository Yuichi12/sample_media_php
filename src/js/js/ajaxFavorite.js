var $ = require('jquery');

function favorite() {
  var $like,
    likePostId;
  $like = $('.js-click-like') || null; // nullというのはnull値という値で、「変数の中身は空ですよ」と明示するために使う値
  likePostId = $like.data('postid') || null;
  // 数値の0はfalseと判定されてしまう。post_idが0の場合もありえるので、0もtrueとする場合にはundefinedとnullを判定する
  if (likePostId !== undefined && likePostId !== null) {
    $like.on('click', function () {
      var $this = $(this);
      $.ajax({
        type: "POST",
        url: "ajaxFavorite.php",
        data: {
          postId: likePostId
        }
      }).done(function (data) {
        console.log('Ajax Success');
        // クラス属性をtoggleでつけ外しする
        $this.toggleClass('active');
      }).fail(function (msg) {
        console.log('Ajax Error');
      });
    });
  }
}

module.exports = favorite;
