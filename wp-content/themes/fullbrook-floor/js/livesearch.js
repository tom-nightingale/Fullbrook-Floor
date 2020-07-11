// binds $ to jquery, requires you to write strict code. Will fail validation if it doesn't match requirements.
(function ($) {
  "use strict";
  // add all of your code within here, not above or below
  $(function () {
    $(document).ready(function () {


      let query = "";
      function liveSearch(query) {
        console.log(query);

        let search_term = "?search_term=" + query;
        const restURL = themeURL.site_url + "/wp-json/livesearch/v2/livesearch/" + search_term;


        $.getJSON(restURL, function (data) {
          //We have results to we can return them!
          if (data) {
            $('.livesearch-results').html(data);
            $('.livesearch-results').removeClass('hidden');
          }
        });
      }

      $(".livesearch").keyup(function (e) {
        let query = $(this).val();
        liveSearch(query);
      });


    });
  });
}(jQuery));