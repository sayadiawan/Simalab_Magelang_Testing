

// To apply the default browser preference instead of explicitly setting it.
// firebase.auth().useDeviceLanguage();



function signup(role) {
  var email=document.getElementById("email").value;
  var password=document.getElementById("password").value;
  var username=document.getElementById("username").value;





  // var user = firebase.auth().currentUser;

  firebase.auth().createUserWithEmailAndPassword(email, password)
  .then((user) => {


    firebase.auth().currentUser.updateProfile({
      displayName: username
    }).then(function() {
        // Update successful.

        if(role=="admin"){
          firebase.database().ref('users/'+user.uid).set({
            username: username,
            email: email,
            role:"admin"
          }).then(function() {
            window.location.href = "./dashboard-admin"
    
         
          }).catch(function(error) {
            // An error happened.
          });
  
        }else{
          firebase.database().ref('users/'+user.uid).set({
            username: username,
            email: email,
            role:"client"
          }).then(function() {
            window.location.href = "./dashboard"
    
         
          }).catch(function(error) {
            // An error happened.
          });
        }
       
    
      
    
      }).catch(function(error) {
        // An error happened.
        console.log(error.message)
      });
      
     
  
    // Signed in 
    // ...
  })
  .catch((error) => {
      var errorCode = error.code;
      var errorMessage = error.message;
      console.log(error.message)
      alert(error.message)
    
    // ..
  });
}
