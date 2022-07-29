let equipments = [
    {
        "id": 11,
        "name": "Cisco IP Phone 7821",
        "idCategory": 2,
        "canBeLoaned": false,
        "imageUrl": "https://media.ldlc.com/r374/ld/products/00/05/14/00/LD0005140072_2.jpg"
    },
    {
        "id": 12,
        "name": "Cisco IP Phone 8851",
        "idCategory": 2,
        "canBeLoaned": false,
        "imageUrl": "https://media.ldlc.com/r1600/ld/prod-ucts/00/03/95/95/LD0003959506_2_0003959562_0005140614.jpg"
    }, 
    {
        "id": 13,
        "name": "Dell Inspiron 15",
        "idCategory": 3,
        "canBeLoaned": true,
        "imageUrl": "https://media.ldlc.com/r1600/ld/prod-ucts/00/05/50/87/LD0005508749_2_0005668376_0005708584.jpg"
    }, 
    {
        "id": 14,
        "name": "Vidéoprojecteur Acer",
        "idCategory": 4,
        "canBeLoaned": true,
        "imageUrl": "https://media.ldlc.com/r1600/ld/products/00/05/64/32/LD0005643283_2.jpg"
    }, 
    {
        "id": 15,
        "name": "Switch NETGEAR 8 ports",
        "idCategory": 1,
        "canBeLoaned": false,
        "imageUrl": "https://media.ldlc.com/r374/ld3/zoom/2010/LD0000827027.jpg"
    }
]

let categories = [
    {
        "id": 1,
        "name": "Réseau"
    },
    {
        "id": 2,
        "name": "Téléphonique"
    },
    {
        "id": 3,
        "name": "Desktop"
    },
    {
        "id": 4,
        "name": "Réunion"
    }
]

var categ = ""
var loaned = ""

// Load les équipements à l'ouverture de la page
window.onload = function(e){ 
    

    equipments.forEach(element => {
        categories.forEach(categorie => {
            if(element.idCategory == categorie.id){
                this.categ = categorie.name
            }
        });

        if(element.canBeLoaned == false){
            loaned = "<i class='fa-solid fa-xmark iconUnvalid'></i>"
        }else{
            loaned = "<i class='fa-solid fa-check iconValid'></i>"
        }

        addRow(element, categ, loaned)
    });
}

// Fonction pour recharger la liste des équipements
function reloadElements(){

    document.getElementById('items').innerHTML = ""

    equipments.forEach(element => {
        categories.forEach(categorie => {
            if(element.idCategory == categorie.id){
                this.categ = categorie.name
            }
        });

        if(element.canBeLoaned == false){
            loaned = "<i class='fa-solid fa-xmark iconUnvalid'></i>"
        }else{
            loaned = "<i class='fa-solid fa-check iconValid'></i>"
        }

        addRow(element, categ, loaned)
    });
}

// Fonction pour ajouter le contenu html d'un équipement
function addRow (element, categorie, loaned) {
    document.querySelector('#items').insertAdjacentHTML(
      'afterbegin',
      `<div class="buttons">
        <div id="${element.id}button" title="Éditer l'équipement ${element.name}" class="buttonEdit" onclick="openEdit(${element.id})" type="submit" data-bs-toggle="modal" data-bs-target="#edit_item"><i class="fa-solid fa-edit"></i></div>
        <div id="${element.id}delete" title="Supprimer l'équipement ${element.name}" class="buttonDelete" onclick="openDelete(${element.id})" type="submit" data-bs-toggle="modal" data-bs-target="#delete_item"><i class="fa-solid fa-trash"></i></div>
      </div> 
      <div id="${element.id}" class="item">
        <span><img class="imageListe" src="${element.imageUrl}" alt="image de l'équipement"></span>
        <span data-label="Catégorie :" class="categorie">${categorie}</span>
        <div class="dispo">
            <span class="disponibilite">Disponibilité ${loaned}</span>
        </div>
        <span data-label="Libelle :" class="libelle">${element.name}</span>
      </div>`      
    )
}

// AJOUTER UN ÉQUIPEMENT

// Reset des values
$( "#addItem" ).click(function() {
    document.getElementById('name').value = "";
    document.getElementById('categ').value = "";
    document.getElementById('loaned').checked = "";
    document.getElementById('image').value = "";
})

// Ajoute l'équipement
function addItem(){

    // Test si le formulaire est valide
    if($("form")[0].checkValidity()){
        var name = document.getElementById('name').value;
        var categ = document.getElementById('categ').value;
        var dispo = document.getElementById('loaned').checked;
        var image = document.getElementById('image').value;
    
        // Création de l'élément "équipement"
        var element = {
            "id": Date.now(),
            "name": name,
            "idCategory": categ,
            "canBeLoaned": dispo,
            "imageUrl": image
        }
    
        // Ajoute l'équipement dans le tableau des équipements
        equipments.push(element);
    
        // Rechargement de la liste des équipements à jour
        reloadElements()
    
        // Fermeture du modal
        $('#add_item').modal('hide');

        // Envoie du message de succès
        sendToast('L\'équipement a été ajouté avec succès', 'success')
    }else{
        sendToast('Le formulaire n\'est pas valide', 'error')
    }

    let myAlert = document.querySelector('.toast')
    let bsAlert = new bootstrap.Toast(myAlert)
    bsAlert.show()
    
}

// ÉDITER UN ÉQUIPEMENT

// Set les values des input à l'ouverture du modal
function openEdit(id){
    
    equipments.forEach(item => {
        if(item.id == id){
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_categ').value = item.idCategory;
            if(item.canBeLoaned == false){
                document.getElementById('edit_loaned').checked = false
            }else{
                document.getElementById('edit_loaned').checked = true
            }
            document.getElementById('edit_image').value = item.imageUrl;

            $(".image_preview").show();
            $(".image_preview").attr("src", item.imageUrl);
        }
    })

    // Si on click sur le bouton valider, on lance la fonction d'édite
    document.getElementById('valid_edit').onclick = function() 
    {
        editItems(id)
    }
    
}

// Édite l'équipement
function editItems(id){

    // Test si le formulaire est valide
    if($("form")[1].checkValidity()){

        // Remplacement des values de l'équipement
        equipments.forEach(item => {
            if(item.id == id){
                item.name = document.getElementById('edit_name').value
                item.idCategory = document.getElementById('edit_categ').value
                item.canBeLoaned = document.getElementById('edit_loaned').checked
                item.imageUrl = document.getElementById('edit_image').value
            }
        });

        // Rechargement des équipements avec la liste à jour
        reloadElements()

        // Fermeture du modal
        $('#edit_item').modal('hide');

        // Envoie du message de succès
        sendToast('L\'équipement a été modifié avec succès', 'success')
    }else{
        sendToast('Le formulaire n\'est pas valide', 'error')
    }

    let myAlert = document.querySelector('.toast')
    let bsAlert = new bootstrap.Toast(myAlert)
    bsAlert.show()
}

// SUPPRIMER UN ÉQUIPEMENT

// À l'ouverture du modal
function openDelete(id){

    // Si on clique sur le bouton de suppression on lance la fonction de suppression
    document.getElementById('valid_delete').onclick = function(){
        deleteItems(id)
    }
}

// Supprime l'équipement
function deleteItems(id){

    // Retire l'équipement de la liste
    $.each(equipments, function(i){
        if(equipments[i].id === id) {
            equipments.splice(i,1);
        }
    });

    // Supprime le content + les boutons edit/delete de l'équipement
    $( "#" + id ).remove();
    $( "#" + id + "button").remove();
    $( "#" + id + "delete").remove();

    // Fermeture du modal
    $('#delete_item').modal('hide');

    // Envoie du message de succès
    sendToast('L\'équipement a été supprimé avec succès', 'success')

    let myAlert = document.querySelector('.toast')
    let bsAlert = new bootstrap.Toast(myAlert)
    bsAlert.show()
}

// FILTRER
function filter(id){
    // Pour chaque équipement
    equipments.forEach(equipment => {
        // On les réaffiche tous
        $( "#" + equipment.id ).show();
        $( "#" + equipment.id + "button").show();
        $( "#" + equipment.id + "delete").show();

        // Puis test sur l'id, si ce n'est pas le bon on cache l'équipement
        if(equipment.idCategory != id){
            $( "#" + equipment.id ).hide();
            $( "#" + equipment.id + "button").hide();
            $( "#" + equipment.id + "delete").hide();
        }
        
    });
}

// Event sur lequel on récupère l'id de la catégorie souhaité
$( "#filterCateg" ).change(function() {
    // Si on choisit une catégorie, on lance la fonction
    if(this.value !== ""){
        filter(this.value)
    }
    // Sinon on réaffiche tous les équipements
    else{
        equipments.forEach(equipment => {
            $( "#" + equipment.id ).show();
            $( "#" + equipment.id + "button").show();
            $( "#" + equipment.id + "delete").show();
        });
    }
})

// TOAST
function sendToast (message, type) {
    document.querySelector('#toasts').insertAdjacentHTML(
      'afterbegin',
      `<div class="toast ${type}" role="alert" data-bs-autohide="true" data-bs-delay="3000" aria-live="assertive" aria-atomic="true">
      <div class="toast-body">
        ${message}
        <a type="button" data-bs-dismiss="toast" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
      </div>
    </div>`      
    )
}

// PREVIEW DE L'IMAGE DANS LES FORMULAIRES

// À l'ouverture du modal d'ajout, on cache la preview
$('#add_item').on('show.bs.modal', function (e) {
    $(".image_preview").hide();
})

// Event sur le changement de la value de l'url
$("#image").change(function() {

    // Check si l'image est valide
    testImg = checkImage(this.value)

    if(testImg == true ){
        // Affiche l'input
        $(".image_preview").show();
        // Ajout de l'url dans le scr de l'input
        $(".image_preview").attr("src", this.value);
    }else{
        $(".image_preview").hide();
    }
});

// Même chose pour l'édit
$("#edit_image").change(function() {

    testImg = checkImage(this.value)

    if(testImg == true ){
        $(".image_preview").show();
        $(".image_preview").attr("src", this.value);
    }else{
        $(".image_preview").hide();
    }
});

// Fonction qui renvoie true si l'url de l'image entrée est valide
function checkImage(url) {
    return (url.match(/^http[^\?]*.(jpg|jpeg|gif|png|tiff|bmp)(\?(.*))?$/gmi) !== null);
}
