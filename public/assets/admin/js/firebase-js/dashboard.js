
var user;

firebase.auth().onAuthStateChanged(user => {


    if(this.role=="admin"){
      database.ref("users/"+user.uid+"/role").once('value').then(function(snapshot) {
        console.log(snapshot.val())
       
        if(snapshot.val()=="admin"){
          this.user=user;
          $("#nama").html("Hai, "+user.displayName);        
        
        }else{
 
          window.location.href = "./sm-admin"
        }
      });
    
      // window.location.href = "./dashboard-admin"
    }else{
      database.ref("users/"+user.uid+"/role").once('value').then(function(snapshot) {
       
        if(snapshot.val()=="client"){
          this.user=user;
          $("#nama").html("Hai, "+user.displayName);
        }else{
        
         
          window.location.href = "./"
        }
      });

      // window.location.href = "./dashboard"
    }
    
  
  
})
// if (user == null) {
//   window.location.href = "./"
// }


talpha.orderByKey().limitToLast(1).once('value').then(function(snapshot) {

    snapshot.forEach(function(child) {        
      var altitude=child.val().altitude;
      var temperature=child.val().temperature;
      var humidity=child.val().humidity;
      var pressure=child.val().pressure;

      $("#altitude").html(altitude)
      $("#temperature").html(temperature)
      $("#humidity").html(humidity)
      $("#pressure").html(pressure)
    });

});

var data_temp=[];

var _5mountsago = Date.now() - (5*30* 24 * 60 * 60 * 1000);

var _4mountsago = Date.now() - (4*30* 24 * 60 * 60 * 1000);


// 1593363383863
      //5184000000
   
// 1600324662463

talpha.orderByChild('timestamp') .startAt(_5mountsago).endAt(_4mountsago).once('value' ,function(snapshot) {

      
      snapshot.forEach(function(child) {
        var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
       
        data_temp.push({x: moment(child.val().timestamp).format("DD-MM-yyyy HH:mm"),y:child.val().temperature})
        
      });


  });



  



  function logout(role) {

    console.log(role);
    firebase.auth().signOut().then(function() {
      // Sign-out successful.

      if(role=="admin"){
        window.location.href = "./sm-admin"
      }else{
        window.location.href = "./"
      }
    }).catch(function(error) {
      // An error happened.
      alert(error)
    });
    

  }



