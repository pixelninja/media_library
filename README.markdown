# Media Library

Currently under construction, but mostly works. I've yet to test this in a live/production environment.

## Installation

- Upload the `/media_library` folder to your Symphony `/extensions` folder.
- Enable it by selecting "Media Library", choose Enable from the with-selected menu, then click Apply.

## Usage

In the main navigation, there should now be a `Media Library` item. Click the icon will take you to the physical page, while clicking anywhere else on the link will open the media library within a lightbox.

To use the media library within the TinyMCE editor, add this snippet to your TinyMCE javascript file:

`
file_picker_types: 'image media',
file_picker_callback: function(callback, value, meta) {
	ml_source_input = callback;
	localStorage.setItem('add-to-editor', 'yes');
	$('#nav .ml-link').trigger('click');
},
`

Now, when clicking the image or media icon within the editor, there will be a file icon next to the source field. Clicking this will open the media library, and instead of `copying to clipboard` being an option, it will say `add to editor`. This will add the file source and the file name to the source and alt fields.

### To do

- Test on production environment, permissions?
- create a Media Library field, which acts in place of the Upload field
- (maybe) option to replace/overwrite an image
- (maybe) Check if files are in use
- (maybe) link with JIT extension for images to copy URL with recipe
- ~~Add in on-the-fly filtering by name~~
- ~~Check if uploads field exists~~
- ~~Upload files?~~
- ~~preview other file types (not just images)~~
- ~~delete files~~
- ~~lightbox on images and videos~~
- ~~Copy URL button~~
- ~~Use AJAX to load subfolders nicely~~
- ~~Use AJAX to load in Library from any page without losing context~~
- ~~Multilingual support~~
- ~~TinyMCE text formatter button~~
