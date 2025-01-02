<?php

/**
  * Handles file download
  *
  **/
class DownloadHandler {
	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->request = $registry->get('request');
		$this->log = $registry->get('log');
	}

	protected function is_external($file) {
		return preg_match('/^(http|ftp|https):\/\//', $file) == 1;
	}

	/**
	  * Return file mime types
	  *
	  **/
	static public function get_mime_types() {
		return array(
			// Applications
			"pdf"   => "application/pdf",
			"exe"   => "application/octet-stream",
			"zip"   => "application/zip",
			"gz"    => "application/gzip",
			"rar"   => "application/x-rar-compressed",
			"tar"   => "application/x-tar",
			"doc"   => "application/msword",
			"docx"  => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
			"xls"   => "application/vnd.ms-excel",
			"xlsx"  => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
			"ppt"   => "application/vnd.ms-powerpoint",
			"pps"   => "application/vnd.ms-powerpoint",
			"pptx"  => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
			"ppsx"  => "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
			"odt"   => "application/vnd.oasis.opendocument.text",
			"odp"   => "application/vnd.oasis.opendocument.presentation",
			"ods"   => "application/vnd.oasis.opendocument.spreadsheet",
			"odg"   => "application/vnd.oasis.opendocument.graphics",
			"jsc"   => "application/javascript",
			"js"    => "application/javascript",
			"ps"    => "application/postscript",
			"ttf"   => "application/x-font-ttf",
			// Image
			"gif"   => "image/gif",
			"jpeg"  => "image/jpeg",
			"jpg"   => "image/jpeg",
			"png"   => "image/png",
			"svg"   => "image/svg+xml",
			"djvu"  => "image/vnd.djvu",
			"djv"   => "image/vnd.djvu",
			// Audio
			"mp3"   => "audio/mpeg",
			"mp4"   => "audio/mp4",
			"wav"   => "audio/vnd.wave",
			"mka"   => "audio/x-matroska",
			"wma"   => "audi/x-ms-wma",
			// Video
			"avi"   => "video/avi",
			"divx"  => "video/avi",
			"mpeg"  => "video/mpeg",
			"mpg"   => "video/mpeg",
			"mpe"   => "video/mpeg",
			"mov"   => "video/quicktime",
			"mkv"   => "video/x-matroska",
			"3gp"   => "video/3gpp",
			"wmv"   => "video/x-ms-wmv",
			"flv"   => "video/x-flv",
			// Text
			"css"   => "text/css",
			"csv"   => "text/csv",
			"php"   => "text/plain",
			"htm"   => "text/html",
			"html"  => "text/html",
			"rtf"   => "text/rtf",
			"xml"   => "text/xml",
		);
	}

	public function download($file, $mask, $callback=null) {
		// Avoid sending unexpected errors to the client as we don't want to corrupt the data
		@error_reporting(0);

		$mime_types = DownloadHandler::get_mime_types();
		$path_parts = pathinfo($mask);
		$external = $this->is_external($file);
		$file_path = DIR_DOWNLOAD . $file;

		if (function_exists("finfo_open") && !$external) {
			$finfo = finfo_open(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME);
			$mime = finfo_file($finfo, $file_path);
			finfo_close($finfo);
		} else if (isset($path_parts['extension']) && array_key_exists(strtolower($path_parts['extension']), $mime_types))
			$mime = $mime_types[strtolower($path_parts['extension'])];
		else
			$mime = 'application/octet-stream';

		if ($external && !headers_sent()) {
			if (is_callable($callback)) {
				$callback();
			}
			header("Location: " . $file);
			exit;
		} else if (!headers_sent()) {
			$file_size = filesize($file_path);
			$handle = @fopen($file_path, "rb");
			if ($handle) {
				// Clean up any buffers and turn off output buffering

				// ob_gzhandler manages http headers.  When undoing it with
				// ob_end_flush, the result is "Warning - headers already sent ..."
				// just bail
				if (in_array('ob_gzhandler', ob_list_handlers())) {
					ob_flush();
				} else {
					// Tell apache to send an uncompressed non-chunked response
					if (!headers_sent() && function_exists('apache_setenv')) {
						apache_setenv('no-gzip', '1');
					}

					// Turn off any default output handlers
					ini_set('output_handler', '');
					ini_set('output_buffering', false);
					ini_set('implicit_flush', true);

					// Clean the output buffer
					$levels = ob_get_level();
					for ($i = 0; $i < $levels; $i++) {
						@ob_end_clean();
					}

					// Turn off the zlib compression handler
					if (!headers_sent() && ini_get('zlib.output_handler')) {
						ini_set('zlib.output_handler', '');
						ini_set('zlib.output_compression', 0);
					}
				}

				// Default to send entire file
				// Offset signifies where we should begin to read the file
				$byte_offset = 0;
				//Length is for how long we should read the file according to the browser, and can never go beyond the file size
				$byte_length = $file_size;

				// Remove headers that might unnecessarily clutter up the output
				header_remove('Cache-Control');
				header_remove('Pragma');

				if ($this->config->get("module_product_downloads_noindex")) {
					header('X-Robots-Tag: none'); // == noindex, nofollow
				}

				header('Accept-Ranges: bytes', true);
				header("Content-Type: $mime", true);

				if ($this->config->get("module_product_downloads_force_download")) {
					header(sprintf('Content-Disposition: attachment; filename="%s"', $mask ? $mask : basename($file_path)));
				} else {
					// Allow streaming
					header(sprintf('Content-Disposition: inline; filename="%s"', $mask ? $mask : basename($file_path)));
				}

				// Check if http_range is sent by browser (or download manager)
				if (isset($this->request->server['HTTP_RANGE'])) {
					$range = $this->request->server['HTTP_RANGE']; // IIS/Some Apache versions
				} else if (function_exists('apache_request_headers') && $apache = apache_request_headers()) { // Try Apache again
					$headers = array();
					foreach ($apache as $header => $val) {
						$headers[strtolower($header)] = $val;
					}
					if (isset($headers['range'])) {
						$range = $headers['range'];
					} else {
						$range = FALSE; // We can't get the header/there isn't one set
					}
				} else {
					$range = FALSE; // We can't find the http_range headers/they are not set
				}

				if ($range) {
					// Validate range request
					if (!preg_match('/^bytes=(\d*-\d*)(,\d*-\d*)*$/', $range)) {
						header('HTTP/1.1 416 Requested Range Not Satisfiable');
						header('Content-Range: bytes */' . $file_size); // Required in 416.
						exit;
					}
					// Figure out download piece from range (if set)
					// Multiple ranges could be specified at the same time, but for simplicity only serve the first range
					// http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
					$ranges = explode(',', substr($range, 6));
					$parts = explode('-', $ranges[0]);

					// Set offset and length based on range
					// Also check for invalid ranges.
					if ($parts[0] === '') { // First number missing, return last $parts[1] bytes
						$byte_length = min((int)$parts[1], $file_size);
						$byte_offset = $file_size - $byte_length;
					} else if ($parts[1] === '') { // Second number missing, return from byte $parts[0] to end
						$byte_offset = min((int)$parts[0], $file_size - 1);
						$byte_length = $file_size - $byte_offset;
					} else {
						$byte_offset = min((int)$parts[0], $file_size - 1);
						$byte_length = min(abs((int)$parts[1] + 1 - $byte_offset), $file_size - $byte_offset);
					}

					header("HTTP/1.1 206 Partial content");
					header(sprintf('Content-Range: bytes %d-%d/%d', $byte_offset, $byte_offset + $byte_length - 1, $file_size)); // Decrease by 1 on byte-length since this definition is zero-based index of bytes being sent
				}

				$byte_range = $byte_length;// - $byte_offset;

				header(sprintf('Content-Length: %d', $byte_range));

				function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
					// error was suppressed with the @-operator
					if (0 === error_reporting()) {
						return false;
					}

					throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
				}
				set_error_handler('handleError');

				try {
					set_time_limit(0);
				} catch (ErrorException $e) {
					if ($this->config->get('config_error_log')) {
						$this->log->write("PD PRO exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
					}
				}
				restore_error_handler();

				$buffer = '';   // Variable containing the buffer
				$buffer_size = 1024 * 8; // Just a reasonable buffer size
				$byte_pool = $byte_range; // Contains how much is left to read of the byte_range

				if (fseek($handle, $byte_offset, SEEK_SET) == -1) {
					header("HTTP/1.0 500 Internal Server Error");
					if ($this->config->get('config_error_log')) {
						$this->log->write('Error: Could not seek to position ' . $byte_offset . ' in file ' . $file_path . '!');
					}
					exit;
				}

				while (!feof($handle) && $byte_pool > 0) {
					$chunk_size_requested = min($buffer_size, $byte_pool); // How many bytes we request on this iteration

					// Try reading $chunk_size_requested bytes from $handle and put data in $buffer
					$buffer = fread($handle, $chunk_size_requested);

					// Store how many bytes were actually read
					$chunk_size_actual = strlen($buffer);

					// If we didn't get any bytes that means something unexpected has happened since $byte_pool should be zero already
					if ($chunk_size_actual == 0) {
						// For production servers this should go in your php error log, since it will break the output
						if ($this->config->get('config_error_log')) {
							$this->log->write(sprintf('Error: Chunksize became 0 while downloading %d-%d/%d of file %s!', $byte_offset, $byte_length - 1, $file_size, $file_path));
						}
						break;
					}

					// Decrease byte pool with amount of bytes that were read during this iteration
					$byte_pool -= $chunk_size_actual;

					// Write the buffer to output
					print $buffer;

					// Try to output the data to the client immediately
					// Only flush when the output buffering is still active and there is data in the buffer
					if (ob_get_level() > 0) ob_flush();
					flush();

					// Stop if connection is closed already
					if (connection_status() != 0) {

						// Update downloaded count only if end of file has been reached
						if (ftell($handle) == $file_size) {
							if (is_callable($callback)) {
								$callback();
							}
						}

						@fclose($handle);
						exit;
					}
				}

				// Update downloaded count only if end of file has been reached
				if (ftell($handle) == $file_size) {
					if (is_callable($callback)) {
						$callback();
					}
				}

				// File save was a success
				@fclose($handle);
				exit;
			} else {
				// File couldn't be opened
				header("HTTP/1.0 500 Internal Server Error");
				if ($this->config->get('config_error_log')) {
					$this->log->write('Error: Could not open file ' . $file_path . '!');
				}
				exit;
			}
		} else {
			exit('Error: Headers already sent!');
		}
	}
}
