//----------------------------------------------------------------------
// Copyright (c) 2015 Raytheon BBN Technologies
//
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and/or hardware specification (the "Work") to
// deal in the Work without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Work, and to permit persons to whom the Work
// is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Work.
//
// THE WORK IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE WORK OR THE USE OR OTHER DEALINGS
// IN THE WORK.
//----------------------------------------------------------------------

// cards.js: enable switching between tabs with nice animations

get_callback = function(tab_name) {
  return function(){};
}

$(document).ready(function() {
  var active, content;
  $('ul.tabs').each(function() {
    var links = $(this).find('a');
    active = $(links.filter('[href="'+location.hash+'"]')[0] || links[0]);
    active.addClass('activesection');
    content = $(active.attr('href'));
    callback = get_callback(active.attr('href'));
    callback();

    links.not(active).each(function() {
      $($(this).attr('href')).hide();
    });


    $(this).on('click', 'a', function(e) {
      active.removeClass('activesection');
      oldindex = active.attr('data-tabindex');

      content.hide();

      var new_active = $(this).attr('href');
      var state = {location: new_active};
      history.pushState(state, '', new_active);
      active = $(this);
      newindex = active.attr('data-tabindex');
      active.addClass('activesection');
      content = $($(this).attr('href'));
      loadingdirection = oldindex < newindex ? "loadingright" : "loadingleft"
      content.show();
      content.addClass(loadingdirection);
      content.show();

      content.each(function(index, element) {
        setTimeout(function() {
          element = $(element);
          element.removeClass(loadingdirection);
        }, 0);
        if(new_active == "#map") {
          map_init("/common/map/current.json", [42, -72], 3);
        }
      });
      callback = get_callback($(this).attr('href'));
      callback();
      e.preventDefault();
    });
  });

  $(window).on("popstate", function(e) {
    if (e.originalEvent.state !== null) {
      active.removeClass('activesection');
      content.hide();
      active = $('a[href="' + e.originalEvent.state.location + '"]');
      content = $(active.attr('href'));
      active.addClass('activesection');
      content.show();
    } else {
      $('ul.tabs').each(function() {
        active.removeClass('activesection');
        content.hide();
        var links = $(this).find('a');
        active = $(links.filter('[href="'+location.hash+'"]')[0] || links[0]);
        active.addClass('activesection');
        content = $(active.attr('href'));
        content.show();
        if(active == "#map") {
          map_init("/common/map/current.json", [42, -72], 3);
        }
      });
    }
  });
});
