
var Page = Spine.Model.sub();

Page.configure("Page", "name", "content");

Page.extend(Spine.Model.Ajax);

Page.extend({

});

jQuery(function($){

  var Pages = Panel.sub({

    elements: {
      "#pages": "content"
    },

    init: function(e) {
      this.render();
    },
    
    render: function() {
      this.html('<ul><li>Installation</li></ul>');
    }
    
  });

  var Wiki = Stage.sub({
    
    events: {
      'tap section': 'enter'
    },
    
    elements: {
    },
    
    enter: function() {
      alert('touchstart');
    },

    init: function(){

      view = new Pages({
        el: $("body")
      });

      view.active();
      
    }

  });
  
  return new Wiki({
    el: $("body")
  });

});