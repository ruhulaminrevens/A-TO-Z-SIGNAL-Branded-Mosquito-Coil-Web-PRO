(function(){
  if(window.gsap) return;
  function nodes(target){
    if(!target) return [];
    if(typeof target === 'string') return Array.prototype.slice.call(document.querySelectorAll(target));
    if(target.length && !target.nodeType) return Array.prototype.slice.call(target);
    return [target];
  }
  function apply(el, vars){
    if(!el || !vars) return;
    if(vars.opacity !== undefined) el.style.opacity = vars.opacity;
    var x = vars.x || 0, y = vars.y || 0, scale = vars.scale || 1, rotateY = vars.rotateY || 0;
    if(x || y || scale !== 1 || rotateY) el.style.transform = 'translate3d('+x+'px,'+y+'px,0) scale('+scale+') rotateY('+rotateY+'deg)';
  }
  window.gsap = {
    to: function(target, vars){ nodes(target).forEach(function(el){ apply(el, vars); }); },
    from: function(target, vars){ nodes(target).forEach(function(el){ apply(el, {opacity:1, x:0, y:0, scale:1, rotateY:0}); }); },
    fromTo: function(target, fromVars, toVars){ nodes(target).forEach(function(el){ apply(el, toVars); }); }
  };
})();
