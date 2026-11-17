---
name: compress-to-webp
description: Convert individual images or a folder of images into optimized WebP files for web use while preserving visual quality and transparency. Trigger when the user asks to compress images to WebP, including “/comprees to webp”.
metadata:
  short-description: Compress images to WebP
---

# Compress images to WebP

Use this workflow when the user invokes `/comprees to webp`, asks to compress images into WebP, or requests a web-ready image batch.

## Conversation flow

Collect the inputs in this order, asking only for details that are still missing:

1. Ask whether the source is **one or more files** or **one folder** using the available structured user-question tool (for example, `request_user_input_async`) with clickable choices. Label the choices `File gambar` and `Satu folder berisi gambar`. Do not ask this as plain assistant prose when the structured question tool is available.
2. For files, use a structured free-text question to ask for the full directory path and each filename (including extension). For a folder, ask for its full path.
3. Use a structured free-text question to ask where the WebP results should be saved.
4. If paths or files cannot be accessed in the current environment, explain that limitation and ask the user to upload the files or use a path available to the active workspace. Do not pretend a local path was read.

Present each missing input as a question card in the chat. Wait for the user's answer before asking the next question or starting conversion. If no structured question tool is available, ask normally in the chat.

Do not ask the user to choose a compression setting unless they request a specific quality or maximum size. Use the defaults below and explain that WebP is lossy by default, so mathematically identical pixels and smaller size cannot both be guaranteed for every image.

## Conversion behavior

- Convert all supported raster image files found in the provided source. Use an installed local image tool/library (prefer Pillow if available; otherwise use ImageMagick or another available decoder that supports WebP). Determine the actual supported formats from the chosen decoder rather than claiming universal format support.
- For a folder, process images in that folder and its subfolders, preserving relative subfolder structure in the destination. Ignore non-image files. Never overwrite source files; write `.webp` outputs only under the destination directory.
- If the destination equals or is inside the source folder, exclude the destination tree from scanning to prevent processing generated files again.
- Default to quality 90, method 6 (or the closest available high-quality/slow setting), with lossy WebP and alpha quality 100 when transparency is present. This gives a strong visual-quality/size balance. Honor an explicitly requested quality or lossless mode.
- Apply EXIF orientation before encoding. Preserve alpha and copy ICC color profiles and supported metadata when the encoder allows it. Avoid flattening transparency or changing color mode unnecessarily.
- Derive output names from the source basename with a `.webp` extension. If multiple inputs would collide at the same destination, choose deterministic unique names and report them.
- Report unreadable or unsupported files individually; continue with other files in a batch.

## Completion report

Summarize the number converted and skipped/failed, output directory, and total input/output sizes with overall size reduction when measurable. Mention the quality setting used. Provide the output file paths or a compact list for a large batch. Do not claim quality is unchanged; say “visually high quality” unless lossless encoding was used.
