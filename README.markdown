# Media Library

This extension aims to be a one stop shop for asset management with Symphony CMS projects. You can view, delete, search, upload and manipulate files into the site uploads folder.

This also comes packaged with a field to replace the default upload field, where files can be uploaded or selected and attached directly to entries.

### Upgrade Notes

#### 3.0

If you are upgrading from a version lower than 3.0, this is a breaking change. File data is now output as nodes when previously they were attributes. Please check the debug carefully and update your xslt to match.**

## Installation

- Upload the `/media_library` folder to your Symphony `/extensions` folder.
- Enable it by selecting "Media Library", choose Enable from the with-selected menu, then click Apply.

`/workspace/uploads` must exist. This is the directory within which this extension looks for files and subdirectories.

The `json/tags.json` file needs to be writable in order to attach tags to files. These are used for filtering within the Media Library.

The `json/alts.json` file needs to be writable in order to attach alt attributes to files. 

**Note that any extension updates will most likely clear the JSON files of any data. Always back these up first.**

## How to use

Firstly, set up the upload defaults in the preferences. These include items like image dimensions and file sizes.

There are two parts to this extension. The Library and the Field.

### Media Library

You should see `Media Library` as a main nav item now. Clicking on the icon will take you to the Media Library page, clicking anywhere else will open the Media Library in a modal box.

##### Uploading a file

In the top right is an upload button. Clicking on this will slide down the drag and drop capable field.

If you have Doka enabled (more on this below), you will see 2 options: Image Editor and Multiple Upload. Use the toggle to switch out upload fields. 

Image editor takes a single image file, and before uploading brings up edit options so you can manipulate the file.

Multiple Upload allows for multiple files of any type.

If you don't have Doka enabled, then you will only have the Multiple Upload option available to you.

Files will automatically upload after you have dropped or selected them, and then the list will refresh once all files have completed.

##### Directories

To the left is a Show Directories button. This toggles any sub directories within the uploads folder. Clicking on one will take you inside that folder and display any files within it. Clicking back, takes you back. Simples.

##### Files

Files are displayed in a list format. The name of the file is on the left, and any meta data is displayed in the middle. Actions are listed on the right.

Files are ordered chronologically, with the latest to be uploaded at the top.

Clicking `tags` will present an input box. Comma separated tags can be entered here, which become searchable using the filter input above the list of files. This will be output with the image path.

Clicking `alt` will present an input box. Type the alt text you wish to use for this image. This will be output with the image path.

`Edit` will open an instance of Doka allowing you to edit an existing image, and choose to rename it or overwrite it. This option won't appear if Doka isn't enabled.

`Preview` opens the file in a new tab.

`Copy file URL` copies the file path to the users clipboard.

`Delete` removes the file completely. Even if it's being used somewhere! Use with caution.


### Media Library Field

This has been designed to replace the default upload field. It allows you to add a field to a section where an image from the Media Library can be attached. This way, an image can be used in multiple places instead of having to upload it multiple times. You can also add field specific file validation:

- Validation Rule: same as other fields. Good for forcing the file type.
- Media Ratio: This is primarily for images, as it forces an aspect ratio. For example, 3:2 will force landscape, 2:3 will force portrait and 1:1 will force square. There are presets you can choose from or you can manually type your own. They need to be colon separated integers.
- Maximum File Size: This will check that the file is less than or equal to a set size. For example, an avatar is normally a small resolution so the file size might not need to be larger than `50KB`, or a thumbnail might be slightly higher resolution but should be less than `100KB`. This forces authors to upload images that are not too large for the web, keeping load times down. They must be integers with a unit type of `B`, `KB`, or `MB`.
- Destination Directory: The folder to open by default, relative to the `/workspace/uploads` folder. 
- Allow selection of multiple files: This is useful when you need to attach multiple files to an entry, e.g in a gallery or a list of files to be downloaded. Selected files can be reordered with drag/drop.
- When Allow Multiple has been selected, checkboxes will appear next to each file to allow rapid selection. Check the files you wish to attach, then click any of the `select file(s)` links

## Image manipulation with Doka.js

To use [Doka](https://pqina.nl/doka/), you must [purchase a licence](https://pqina.nl/doka/pricing/) and upload the files to a folder called `doka` in the root. I.e `/doka/doka.min.js` and `/doka/doka.min.css`. The extension looks for this file path to confirm Doka integration.

This plugin is great, as it gives authors the ability to upload an image and manipulate it to suit the needs. For example, they may upload an image for a hero image. Then they may upload the same image and crop it to use as a thumbnail. This means they don't need to use expensive or complicated editing software to make web ready imagery.

Read the [documentation](https://pqina.nl/doka/docs/) if you wish to modify settings and options. 

### TinyMCE Integration

Another useful tool is the ability to attach files within WYSIWYG text editors. For example, within news articles.

To use the media library within the TinyMCE editor, add this snippet to your TinyMCE javascript file within the init function:

```
file_picker_types: 'image media',
file_picker_callback: function(callback, value, meta) {
	ml_source_input = callback;
	localStorage.setItem('add-to-editor', 'yes');
	$('#nav .ml-link').trigger('click');
},
```

Now, when clicking the image or media icon within the editor, there will be a file icon next to the source field. Clicking this will open the media library, and instead of `Copy file URL` being an option, it will say `add to editor`. This will add the file source and the file name to the source and alt fields.

## Wishlist

- Add minimum/maximum file validation to multi mode
- Check if a file is in use before permanently deleting it.
