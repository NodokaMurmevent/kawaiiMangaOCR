/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)


// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';


import './styles/stable.css';
import Iconify from '@iconify/iconify';
import * as tocbot from 'tocbot';

//import hljs from 'highlight.js';

document.addEventListener("DOMContentLoaded", () => {
//   hljs.highlightAll();
    tocbot.init({
        // Where to render the table of contents.
        tocSelector: '.js-toc',
        // Where to grab the headings to build the table of contents.
        contentSelector: '.js-toc-content',
        ignoreSelector: '.js-toc-ignore',
        // Which headings to grab inside of the contentSelector element.
        headingSelector: 'h2, h3, h4',
        // For headings inside relative or absolute positioned containers within content.
        hasInnerContainers: true,
    });
    
});