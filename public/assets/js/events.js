$( document ).ready( function() {

  // init Isotope
  var $container = $('.events_grid').isotope({
    itemSelector: '.events_grid_item',
    layoutMode: 'fitRows'
  });

 var dateFilter = '';

 $('#filters').on( 'click', 'button', function() {
    dateFilter = $( this ).attr('data-filter');
    filterChecked();
  });

  var filterChecked = function() {
    console.log(dateFilter);
    filterBy = [];
    tracks = $('#filters .tracks input:checked');
    if (tracks.length > 0) {
      $.each(tracks, function(i, val) { 
        if (val.value == '.ride') {
          difficulty = $('#filters .difficulty input:checked');
          if (difficulty.length > 0) {
            $.each(difficulty, function(j, rd) {
              filterBy.push(dateFilter + val.value + rd.value);
            });
          } else {
            filterBy.push(dateFilter + val.value);
          }
        } else {
          filterBy.push(dateFilter + val.value); 
        }
      });
    } else {
      filterBy.push(dateFilter);
    }

    console.log(filterBy);
    $container.isotope({ filter: filterBy.join() });
  };

 $('#filters input').on('click', filterChecked);

  // change is-checked class on buttons
  $('.dates-group').each( function( i, buttonGroup ) {
    var $buttonGroup = $( buttonGroup );
    $buttonGroup.on( 'click', 'button', function() {
      $buttonGroup.find('.is-checked').removeClass('is-checked');
      $( this ).addClass('is-checked');
    });
  });

});

