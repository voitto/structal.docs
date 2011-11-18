
var Page = Spine.Model.sub();

Page.configure("Page", "name", "body");

Page.extend(Spine.Model.Ajax);

Page.extend({

});

jQuery(function($){
  
  var Pages = Spine.Controller.sub({
    
    init: function() {

      this.pages = new PageList({ el:$("body") });
      this.item = new PageItem({ el:$("body") });
      this.edit = new PageEdit({ el:$("body") });
      
      this.routes({
        "/pages/:id/edit": function(params){
         this.edit.active(params);
        },
        "/pages/:id": function(params){
         this.item.active(params);
        },
        "/": function(params){
         this.pages.active(params);
         Page.fetch();
        }
      });

      this.navigate( '/' );

    }
    
  });
  
  var PageItem = Spine.Controller.sub({
    
    events: {
      'click .back': 'back',
      'click .edit': 'edit',
      'click .destroy': 'destroyItem'
    },
    
    elements: {
      "#content": "content"
    },

    init: function() {

      Page.bind( 'change', function(item){
        if (item.eql(this.item))
          this.render();
      });
      
      this.active = function(params) {
        this.change(Page.find(params.id));
      }
      
    },
    
    change: function(page) {
      this.item = page;
      this.render();
    },
    
    render: function(e) {
      var el = this;
      $.get('tpl/pages/_show.html', function(data) {
        if (!(el.item == undefined))
          $('#content').html(Mustache.to_html(data,el.item));
      });

      return this;
    },
    
    edit: function() {
      this.navigate( '/pages', this.item.id, 'edit' );
    },
    
    back: function() {
      this.navigate( '/' );
    },
    
    destroyItem: function() {
      this.item.destroy();
      this.back();
    }
    
  });

  var PageEdit = Spine.Controller.sub({

    events: {
      "submit form": "update"
    },

    elements: {
      "#pages": "content"
    },

    init: function() {
      this.active = function(params) {
        this.change(Page.find(params.id));
      }
    },
    
    render: function(e) {
      var el = this;
      $.get('tpl/pages/_edit.html', function(data) {
        if (!(el.item == undefined)){
          $('#content').html(Mustache.to_html(data,el.item));
          var config = {};
        	editor = CKEDITOR.appendTo( 'editor', config, el.item.body );
        	
        }
      });
      return this;
    },
    
    change: function(page) {
      this.item = page;
      this.render();
    },
    
    update: function(e) {
      e.preventDefault();
      this.item.updateAttributes({
        name: $(e.target).find("[name=name]").val(),
        body: editor.getData()
      });
      this.back();
    },
    
    back: function() {
      this.navigate( '/pages', this.item.id );
    }
    
  });
      
  var PageList = Spine.Controller.sub({
    
    events: {
      'click  .create': 'create',
      'click .item': 'show'
    },
    
    elements: {
      "#pages": "items"
    },

    init: function() {
      var el = this;
      Page.bind( 'refresh change', this.proxy(this.render));
      setInterval(this.poll, 5*1000);
    },
    
    render: function() {
      var el = this;
      el.items.html('');
      $.get('tpl/pages/_item.html', function(data) {
        items = Page.all();
        for (i in items)
          el.items.prepend(Mustache.to_html(data,items[i]));
      });
      return this;
    },
    
    show: function(e) {
      this.navigate( '/pages', $(e.target).attr('id') );
    },
    
    create: function() {
      item = Page.create({ name:'Untitled page', body:'' });
      this.navigate( '/pages', item.id, 'edit' );
    },
    
    poll: function() {
      $.ajax({
        contentType: 'application/json',
        dataType: 'json',
  			type : 'GET',
        url : '?class=pages&method=changes',
        success : function(data) {
          var pageid = [];
          Page.each(function(p) {
            pageid.push(p.id);
          });
          for (n in data.results) {
            if (-1 == ($.inArray(data.results[n].id, pageid))) {
              $.ajax({
                contentType: 'application/json',
                dataType: 'json',
                type: 'GET',
                url: '?class=pages&id='+data.results[n].id,
                success: function(data){
                  Page.create({
                    name: data[0].name,
                    body: data[0].body,
                    id: data[0].id
                  });
                }
              });
            }
          }
        }
      });
    },
    
  });

  return new Pages();

});