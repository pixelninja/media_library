# Media Library

An easy way to manage your uploads.

## Installation

- Upload the `/media_library` folder to your Symphony `/extensions` folder.
- Enable it by selecting "Media Library", choose Enable from the with-selected menu, then click Apply.

## Usage

In the Preferences, there are options to update the default image options. These include min/max image width/height and image quality.

In the main navigation, there should now be a `Media Library` item. Clicking the icon will take you to the physical page, while clicking anywhere else on the link will open the media library within a lightbox.

With this extension you can:

- Upload multiple files at once using drag/drop
- See all files within the `/workspace/uploads` directory
- Navigate through subdirectories
- Preview files
- Copy the file path to your clipboard
- Delete files
- Filter files by keyword (min 3 characters)
- Tag files with comma separated keywords, for use in filtering files
- Combine with the Tiny MCE editor to create a filepicker option within image/media options
- Use the Media Library Field type in place of the upload field
- Media Library field can take single files or multiple files. Multiple files can be drag/drop reordered
- Edit/manipulate images with [Doka](https://pqina.nl/doka/) (licence required)

## Developers

### Tagging

To use tagging, the `tags.json` file needs to be writable. Make sure the file permissions and file ownership allow this.

### TinyMCE Integration

To use the media library within the TinyMCE editor, add this snippet to your TinyMCE javascript file within the init function:

`
file_picker_types: 'image media',
file_picker_callback: function(callback, value, meta) {
	ml_source_input = callback;
	localStorage.setItem('add-to-editor', 'yes');
	$('#nav .ml-link').trigger('click');
},
`

Now, when clicking the image or media icon within the editor, there will be a file icon next to the source field. Clicking this will open the media library, and instead of `copying to clipboard` being an option, it will say `add to editor`. This will add the file source and the file name to the source and alt fields.

### Doka

To use Doka, you must purchase a licence (https://pqina.nl/doka/) and upload the files to a folder called `doka` in the root. E.g `/doka/doka.min.js` and `/doka/doka.min.css`. The extension looks for this file path to confirm Doka integration.

Crop ratios can be edited/added/removed within the `cropAspectRatioOptions` object.

## To do

- (maybe) Check if files are in use
- ~~Add image manipulation tool for editing on the fly, e.g [Filepond](https://github.com/pqina/filepond) with Doka.js integration
- ~~Create a multiple image field, to attach multiple images to an entry, e.g gallery or slider~~
- ~~Test on production environment, permissions?~~
- ~~create a Media Library field, which acts in place of the Upload field~~
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
- ~~key word Filtering~~
- ~~Tagging for better filtering~~
