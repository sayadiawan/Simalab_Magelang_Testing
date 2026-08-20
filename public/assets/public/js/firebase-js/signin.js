

// var email=document.getElementById("email").value;
// var password=document.getElementById("password").value;
// var email=document.getElementById("username").value;




// To apply the default browser preference instead of explicitly setting it.
// firebase.auth().useDeviceLanguage();



firebase.auth().onAuthStateChanged(user => {
  if (user) {

    if(this.role=="admin"){
      database.ref("users/"+user.uid+"/role").once('value').then(function(snapshot) {
       
       
        if(snapshot.val()=="admin"){
          // this.user=user;
          // $("#nama").html("Hai, "+user.displayName);        
          window.location.href = "./dashboard-admin"
        }
      });
    
      // window.location.href = "./dashboard-admin"
    }else{

      database.ref("users/"+user.uid+"/role").once('value').then(function(snapshot) {
      
        if(snapshot.val()=="client"){
          // this.user=user;
          // $("#nama").html("Hai, "+user.displayName);     
          window.location.href = "./dashboard"   
   
        }
      });
    }
    
  }
  
})



function signin(role){
  var email = document.getElementById("email").value;
  var password = document.getElementById("password").value;
    firebase.auth().signInWithEmailAndPassword(email, password)
    .then((user) => {

    // database.ref("users/")

      if(role=="admin"){
        database.ref("users/"+user.uid+"/role").once('value').then(function(snapshot) {
          console.log(snapshot.val());
          if(snapshot.val()=="admin"){
            window.location.href = "./dashboard-admin"
          }else{
            alert("bukan admin")
           
          }
        });
      
        // window.location.href = "./dashboard-admin"
      }else{
        database.ref("users/"+user.uid+"/role").once('value').then(function(snapshot) {
          console.log(snapshot.val());
          if(snapshot.val()=="client"){
            window.location.href = "./dashboard"
          }else{
            alert("bukan client")
           
          }
        });
  
        // window.location.href = "./dashboard"
      }
      
      // Signed in 
      // ...
    })
    .catch((error) => {
      var errorCode = error.code;
      var errorMessage = error.message;
      alert(errorMessage)
        
    });
}




function google() {

    var provider = new firebase.auth.GoogleAuthProvider();
    provider.addScope('https://www.googleapis.com/auth/contacts.readonly');
    firebase.auth().languageCode = 'id';
    firebase.auth().signInWithPopup(provider).then(function(result) {
     
        // This gives you a Google Access Token. You can use it to access the Google API.
         firebase.auth().onAuthStateChanged(function(user) {
         
            if (user) {
              // User is signed in.


           
                firebase.database().ref('users/'+user.uid).set({
                  username: user.displayName,
                  email: user.email
                }).then(function() {
                  window.location.href = "./dashboard"
              
              
                }).catch(function(error) {
                  alert(error.message)
                  // An error happened.
                });
            } else {
              // No user is signed in.
              // window.location.href = "."
            }
        });
        
        
        // ...
      }).catch(function(error) {
        // Handle Errors here.
        var errorCode = error.code;
        var errorMessage = error.message;
        // The email of the user's account used.
        var email = error.email;
        // The firebase.auth.AuthCredential type that was used.
        var credential = error.credential;
        alert(error.message)
        // ...
      });
      
  }


  function facebook() {

    var provider = new firebase.auth.FacebookAuthProvider();
    provider.addScope('user_birthday');

    firebase.auth().languageCode = 'id';

   
      
    firebase.auth().signInWithPopup(provider).then(function(result) {
        // This gives you a Google Access Token. You can use it to access the Google API.
      
        // The signed-in user info.
        firebase.auth().onAuthStateChanged(function(user) {
            if (user) {
              // User is signed in.
              firebase.database().ref('users/'+user.uid).set({
                username: user.displayName,
                email: user.email,
                role:"client"
              }).then(function() {
                window.location.href = "./dashboard"
            
            
              }).catch(function(error) {
                // An error happened.
              });
            } else {
              // No user is signed in.
              window.location.href = "."
            }
        });
        
        
        // ...
      }).catch(function(error) {
        // Handle Errors here.
        var errorCode = error.code;
        var errorMessage = error.message;
        // The email of the user's account used.
        var email = error.email;
        // The firebase.auth.AuthCredential type that was used.
        var credential = error.credential;
        // ...
        alert(error.message)
      });
      
  }


  function twitter() {
      
    
    var provider = new firebase.auth.TwitterAuthProvider();

    firebase.auth().languageCode = 'id';
   
      
    firebase.auth().signInWithPopup(provider).then(function(result) {
        // This gives you a Google Access Token. You can use it to access the Google API.
    
        firebase.auth().onAuthStateChanged(function(user) {
            if (user) {
              // User is signed in.
              console.log(user)
              firebase.database().ref('users/'+user.uid).set({
                username: user.displayName,
                email: user.email,
                role:"client"
              }).then(function() {
                // window.location.href = "./dashboard"
            
            
              }).catch(function(error) {
                // An error happened.
              });
            } else {
              // No user is signed in.
              window.location.href = "."
            }
        });
        
        
        // ...
      }).catch(function(error) {
        // Handle Errors here.
        var errorCode = error.code;
        var errorMessage = error.message;
        // The email of the user's account used.
        var email = error.email;
        // The firebase.auth.AuthCredential type that was used.
        var credential = error.credential;
        // ...
        alert(error.message)
      });
      
  }
  
  