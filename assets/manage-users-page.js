/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/manage-users-page.css';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
 //$(document).ready(function (e) {

    $.ajax("http://127.0.0.1:8000/manage-users",   // request url
    {            
        success: function (data, status, xhr) {
                console.log(data);    
        }
    });


 
//});