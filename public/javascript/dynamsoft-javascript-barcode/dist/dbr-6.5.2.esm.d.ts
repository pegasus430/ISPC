/**
 * Dynamsoft JavaScript Library
 * @product Dynamsoft Barcode Reader JS Edition
 * @website http://www.dynamsoft.com
 * @preserve Copyright 2019, Dynamsoft Corporation
 * @author Dynamsoft
 * @version 6.5.2
 * @fileoverview Dynamsoft JavaScript Library for Barcode Reader
 * More info on DBR JS: https://www.dynamsoft.com/Products/barcode-recognition-javascript.aspx
 */
//https://github.com/TypeStrong/typedoc to build doc


/**
 * Basic use:
 * ```html
 * <script src='dbr-<version>.min.js'></script>
 * <script>
 *   // https://www.dynamsoft.com/CustomerPortal/Portal/TrialLicense.aspx
 *   dbr.licenseKey = 't0068MgAAAFp16mlue6QjhALowH1tFQxdNVNrtUjMSK8RDezLrZkGAP1TelJQ0cLBLi/BxG3ksSIsZjY3IJX+UVauIe5Zdh4=';
 *   dbr.createInstance().then(reader => reader.decode('./TestSmall.jpg')).then(r=>{console.log(r)});
 * </script>
 * ```
 */
declare class BarcodeReader{
    /** 
     * Set the global licenseKey. If you do not specify a new licenseKey when creating a `BarcodeReader` object, the global licenseKey will be used.
     * Refer to https://www.dynamsoft.com/CustomerPortal/Portal/TrialLicense.aspx to get a try licenseKey.
     * ```js
     * dbr.licenseKey = 't******';
     * ```
     */
    static licenseKey?: string;
    /** 
     * Manually load and compile the decoding module. Used for preloading to avoid taking too long for lazy loading.
     * Whether you have not started loading, loading, success, or failure, you can safely call `loadWasm` repeatedly. 
     * If it has already been loaded, it will return to success immediately.
     * ```js
     * dbr.loadWasm().then(() => { console.log('success'); }, ex => { console.error(ex); });
     * ```
     */
    static loadWasm(): Promise<void>;
    /**
     * A callback when wasm download success in browser environment.
     * ```js
     * dbr._onWasmDownloaded = () => { console.log('downloaded'); };
     * ```
     */
    static _onWasmDownloaded: () => void;
    /**
     * Determine if the decoding module has been loaded successfully.
     * ```js
     * console.log('Is the loading completed? ' + dbr.isLoaded());
     * ```
     */
    static isLoaded(): boolean;
    /**
     * Create a BarcodeReader object. Can be used only after loading the decoding module.
     * @param licenseKey 
     * Set the instance licenseKey. If you do not specify a new licenseKey when creating a `BarcodeReader` instance, the global licenseKey will be used.
     * Refer to https://www.dynamsoft.com/CustomerPortal/Portal/TrialLicense.aspx to get a try licenseKey.
     */
    constructor(licenseKey?: string);
    /**
     * Create a BarcodeReader object. If the decoding module is not loaded, it will be loaded automatically.
     * ```js
     * let reader = undefined;
     * dbr.createInstance().then(r => { reader = r; }, ex => { console.error(ex); });
     * ```
     * @param licenseKey 
     * Set the instance licenseKey. If you do not specify a new licenseKey when creating a `BarcodeReader` object, the global licenseKey will be used.
     * Refer to https://www.dynamsoft.com/CustomerPortal/Portal/TrialLicense.aspx to get a try licenseKey.
     */
    static createInstance(licenseKey?: string): Promise<BarcodeReader>;
    /**
     * Destructor the `BarcodeReader` object.
     * When the `BarcodeReader` object is created, it will open up a space in memory that cannot be automatically reclaimed by js garbage collection.
     * You can manually call this method to destruct the `BarcodeReader` object to release this memory.
     * ```js
     * // delete the instance when you don't need it any more.
     * reader.deleteInstance();
     * ```
     */
    deleteInstance(): void;
    /**
     * The main decoding method can accept a variety of data types, including binary data, images, base64, urls, etc.<br>
     * * In the nodejs environment:<br>
     * &nbsp;&nbsp;The method can accept Buffer, Uint8Array,<br>
     * &nbsp;&nbsp;base64 with mimetype, disk relative paths, disk pair paths, and absolute URLs.<br>
     * &nbsp;&nbsp;All data should be encoded as either jpg, png, bmp or gif.<br>
     * * In the browser environment:<br>
     * &nbsp;&nbsp;The method can accept Blob, ArrayBuffer,<br>
     * &nbsp;&nbsp;Uint8Array, Uint8ClampedArray, HTMLImageElement, HTMLCanvasElement, HTMLVideoElement,<br>
     * &nbsp;&nbsp;base64 with mimetype, relative URLs, and absolute URLs.<br>
     * &nbsp;&nbsp;For URLs you need to handle cross-domain issues yourself, otherwise the promise will fail.<br>
     * &nbsp;&nbsp;Except HTMLCanvasElement and HTMLVideoElement, which have their own format,<br>
     * &nbsp;&nbsp;the other Data should be encoded as either jpg, png, bmp or gif.<br>
     * ```js
     * // decode file in nodejs, or decode url in web
     * reader.decode('./TestSmall.jpg')).then(r=>{console.log(r)});
     * // decode blob from input[type="file"] in web
     * reader.decode(inputEl.files[0]).then(r=>{console.log(r)});
     * ```
     * @param source 
     */
    decode(source: Blob | Buffer | ArrayBuffer | Uint8Array | 
        Uint8ClampedArray | HTMLImageElement | HTMLCanvasElement |
        HTMLVideoElement | string): Promise<BarcodeReader.TextResult[]>;
    /**
     * Take a frame from the video, and then decode it.
     * ```js
     * reader.decodeVideo(document.getElementById('myvideo')).then(r=>{console.log(r)});
     * ```
     * @param video 
     */
    decodeVideo(video: HTMLVideoElement): Promise<BarcodeReader.TextResult[]>;
    /**
     * Take a frame from the video, zoom it to the specified size, and then decode it.
     * ```js
     * reader.decodeVideo(videoEl, videoEl.videoWidth / 2, videoEl.videoHeight / 2)
     *   .then(r=>{console.log(r)});
     * ```
     * @param video 
     * @param dWidth 
     * @param dHeight 
     */
    decodeVideo(video: HTMLVideoElement, 
        dWidth: number, dHeight: number): Promise<BarcodeReader.TextResult[]>;
    /**
     * Take a frame from the specified area of the video, zoom it to the specified size, and then decode it.
     * ```js
     * // decode the area in the upper left corner of 320*240
     * reader.decodeVideo(videoEl, 0, 0, 320, 240, 320, 240).then(r=>{console.log(r)});
     * ```
     * @see [drawImage](https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D/drawImage).
     * @param video 
     * @param sx 
     * @param sy 
     * @param sWidth 
     * @param sHeight 
     * @param dWidth 
     * @param dHeight 
     */
    decodeVideo(video: HTMLVideoElement, sx: number, sy: number,
        sWidth: number, sHeight: number,
        dWidth: number, dHeight: number): Promise<BarcodeReader.TextResult[]>;
    /**
     * Decode base64 type image data with or without minetype.
     * ```js
     * // base64 with mime
     * reader.decodeBase64String('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAqCAYAAA******')).then(r=>{console.log(r)});
     * // base64 without mime
     * reader.decodeBase64String('iVBORw0KGgoAAAANSUhEUgAAACoAAAAqCAYAAA******')).then(r=>{console.log(r)});
     * ```
     * @param base64Str 
     */
    decodeBase64String(base64Str: string): Promise<BarcodeReader.TextResult[]>;
    /**
     * Decode the raw data from the image acquisition device / the image conversion.
     * Such as binary, binaryinverted, grayscaled, NV21, RGB555, RGB565, RGB888, ARGB888.
     * ```js
     * reader.decodeBuffer(rawImgData, rawImgWidth, rawImgHeight, rawImgWidth * 4, dbr.EnumImagePixelFormat.IPF_ARGB_8888).then(r=>{console.log(r)});
     * ```
     * @param source 
     * @param width 
     * @param height 
     * @param stride 
     * @param enumImagePixelFormat 
     */
    decodeBuffer(source: Blob | ArrayBuffer | Uint8Array | Uint8ClampedArray,
        width: number, height: number, stride: number,
        enumImagePixelFormat: BarcodeReader.EnumImagePixelFormat): Promise<BarcodeReader.TextResult[]>;
    /**
     * Get current settings and saves it into a struct.
     * ```js
     * let settings = reader.getRuntimeSettings();
     * ```
     */
    getRuntimeSettings(): BarcodeReader.RuntimeSettings;
    /**
     * Update runtime settings with a given struct.
     * ```js
     * settings.mAntiDamageLevel = 3;
     * settings.mDeblurLevel = 9;
     * reader.updateRuntimeSettings(settings);
     * ```
     * @param settings 
     */
    updateRuntimeSettings(settings: BarcodeReader.RuntimeSettings): Promise<void>;
    /**
     * Reset all parameters to default values.
     * ```js
     * reader.resetRuntimeSettings();
     * ```
     */
    resetRuntimeSettings(): void;
    /**
     * Get all localization barcode results. It contains all recognized barcodes and unrecognized barcodes.
     * ```js
     * reader.decode('xxxx').then(r=>{
     *   console.log(r);
     *   console.log(reader.getAllLocalizationResults());
     * });
     * ```
     */
    getAllLocalizationResults(): BarcodeReader.LocalizationResult[];
}
declare namespace BarcodeReader{

    interface TextResult{
        /**
         * The barcode text.
         */
        BarcodeText: string;
        /**
         * The barcode format.
         */
        BarcodeFormat: number | EnumBarcodeFormat;
        /**
         * Barcode type in string.
         */
        BarcodeFormatString: string;
        /**
         * The barcode content in a byte array.
         */
        BarcodeBytes: number[];
        /**
         * The corresponding localization result.
         */
        LocalizationResult: LocalizationResult;
        /**
         * The original video canvas, existed when using a instance of class `Scanner` and set `bAddOriVideoCanvasToResult` as true.
         */
        oriVideoCanvas?: HTMLCanvasElement;
        /**
         * The search region canvas, existed when using a instance of class `Scanner` and set `bAddSearchRegionCanvasToResult` as true.
         */
        searchRegionCanvas?: HTMLCanvasElement;
    }

    interface LocalizationResult{
        /**
         * The angle of a barcode. Values range from 0 to 360.
         */
        Angle: number;
        /**
         * The document name the barcode located in.
         */
        DocumentName: string;
        /**
         * The barcode module size (the minimum bar width in pixel).
         */
        ModuleSize: number;
        /**
         * The page number the barcode located in. The index is 0-based.
         */
        PageNumber: number;
        /**
         * The region name the barcode located in.
         */
        RegionName: string;
        /**
         * The stage when the results are returned.
         */
        TerminateStage: EnumTerminateStage;
        /**
         * The X coordinate of the left-most point.
         */
        X1: number;
        /**
         * The X coordinate of the second point in a clockwise direction.
         */
        X2: number;
        /**
         * The X coordinate of the third point in a clockwise direction.
         */
        X3: number;
        /**
         * The X coordinate of the fourth point in a clockwise direction.
         */
        X4: number;
        /**
         * The Y coordinate of the left-most point.
         */
        Y1: number;
        /**
         * The Y coordinate of the second point in a clockwise direction.
         */
        Y2: number;
        /**
         * The Y coordinate of the third point in a clockwise direction.
         */
        Y3: number;
        /**
         * The Y coordinate of the fourth point in a clockwise direction.
         */
        Y4: number;
        /**
         * The extended result array.
         */
        ExtendedResultArray: ExtendedResult[];
    }
    
    interface ExtendedResult{
        /**
         * The confidence of the result.
         */
        Confidence: number;
        /**
         * Extended result type.
         */
        ResultType: EnumResultType;
    }

    /**
     * @see [[RuntimeSettingsForScannerConfig]]
     */
    interface RuntimeSettings{
        /**
         * The degree of anti-damage of the barcode. This value decides how many localization algorithms will be used. To ensure the best results, the value of AntiDamageLevel is suggested to be set to 9 if the ExpectedBarcodesCount is set to 0 or 1; otherwise, the value of AntiDamageLevel is suggested to be set to 7.
         */
        mAntiDamageLevel: number;
        /**
         * The types of barcode to be read. Barcode types can be combined as an array. For example, if you want to choose Code_39 and Code_93, you can set it to `EnumBarcodeFormat.CODE_39 | EnumBarcodeFormat.CODE_93`.
         */
        mBarcodeFormatIds: number | EnumBarcodeFormat;
        /**
         * The ink colour for barcodes search.
         */
        mBarcodeInvertMode: number;
        /**
         * The block size for the process of binarization. Block size means the size of a pixel neighbourhood that is used to calculate a threshold value for the pixel.
         */
        mBinarizationBlockSize: number;
        /**
         * Whether to convert colour images. Recommend setting it to "Auto" if you want to pre-detect the barcode regions.
         */
        mColourImageConvertMode: number;
        /**
         * The degree of blurriness of the barcode. The higher value you set, the much more effort the library will take to decode images, but it may also slow down the recognition process.
         */
        mDeblurLevel: number;
        /**
         * For barcodes with a large module size there might be a vacant area in the position detection pattern after binarization which may result in a decoding failure. Setting this to true will fill in the vacant area with black and may help to decode it successfully.
         */
        mEnableFillBinaryVacancy: number;
        /**
         * The expected number of barcodes to read for each image (or each region of the image if you specified barcode regions).
         */
        mExpectedBarcodesCount: number;
        /**
         * The sensitivity used for gray equalization. The higher the value, the more likely gray equalization will be activated. Effective for images with low comparison between black and white colour. May cause adverse effect on images with high level of black and white colour comparison.
         */
        mGrayEqualizationSensitivity: number;
        /**
         * The priority of localization algorithms.
         */
        mLocalizationAlgorithmPriority: string;
        /**
         * The amount of image processing algorithm threads used to decode barcodes.
         */
        mMaxAlgorithmThreadCount: number;
        /**
         * The maximum number of barcodes to read.
         */
        mMaxBarcodesCount: number;
        /**
         * The maximum dimension of full image as barcode zone. Sets the maximum image dimension (in pixels) to localize barcode on the full image. If the image dimension is smaller than the given value, the library will localize barcode on the full image. Otherwise, "FullImageAsBarcodeZone" mode will not be enabled.
         */
        mMaxImageDimensionToLocalizeBarcodesOnFullImage: number;
        /**
         * The output image resolution. When you are trying to decode a PDF file using DecodeFile method, the library will convert the pdf file to image(s) first, then perform barcode recognition.
         */
        mPDFRasterDPI: number;
        /**
         * Values that represent region predetection modes
         */
        mRegionPredetectionMode: number;
        /**
         * Reserved memory for struct. The length of this array indicates the size of the memory reserved for this struct.
         */
        mReserved: string;
        /**
         * The threshold value of the image shrinking. If the shorter edge size is larger than the given value, the library will calculate the required height and width of the barcode image and shrink the image to that size before localization. Otherwise, it will perform barcode localization on the original image.
         */
        mScaleDownThreshold: number;
        /**
         * The text filter mode for barcodes search.
         */
        mTextFilterMode: number;
        /**
         * The sensitivity for texture detection. The higher value you set, the more efforts it will take to detect texture.
         */
        mTextureDetectionSensitivity: number;
        /**
         * The maximum amount of time (in milliseconds) it should spend searching for a barcode per page. It does not include the time taken to load/decode an image (Tiff, PNG, etc) from disk into memory.
         */
        mTimeout: number;
    }
    /** 
     * Basic use:
     * ```js
     * var videoReader = new dynamsoft.BarcodeReader.Scanner({
     *   onFrameRead:function(results){console.log(results);},
     *   onNewCodeRead:function(txt, result){alert(txt);}
     * });
     * videoReader.open();
     * ```
    */
    class Scanner{
        /**
         * Create a `Scanner` object. Can be used only after loading the decoding module.
         * @param config 
         * Optional `config` can be added when creating a `Scanner` instance.
         */
        constructor(config?: ScannerConfig);
        /**
         * Destructor the `Scanner` object.
         * When the `Scanner` object is created, it will open up a space in memory that cannot be automatically reclaimed by js garbage collection.
         * You can manually call this method to destruct the `Scanner` object to release this memory.
         * ```js
         * // delete the instance when you don't need it any more.
         * scanner.deleteInstance();
         * ```
         */
        deleteInstance: () => void;
        /**
         * Check if the scanner is open.
         * ```js
         * console.log(scanner.isOpen());
         * ```
         */
        isOpen: () => boolean;
        /**
         * The HTML element that will contain the scanner object should you choose to customize the UI. It will have a simple default UI if you keep `htmlElement` undefined in initialization config.
         * ```js
         * scanner.htmlElement.appendChild(aDiv);
         * // Warning: Avoid changing the binding if the scanner is already open.
         * scanner.htmlElement = aVideoContainer;
         * ```
         */
        htmlElement: HTMLElement;
        /**
         * Video play settings.
         * Refer [MediaStreamConstraints](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia#Syntax).
         * If during reading, need play again to make it effective.
         * ```js
         * scanner.videoSettings = { video: { width: 1280, height: 720, facingMode: "environment" } };
         * ```
         */
        videoSettings?: MediaStreamConstraints;
        /**
         * This property is mainly related to 1D barcodes.
         * If the confidence of a 1D barcode result is greater than or equal to this `confidence`, that is a reliable result that might cause `onNewCodeRead`.
         * Otherwise if the confidence of a result is smaller than this `confidence`, the result will be ignored by `onNewCodeRead`.
         * Default 30.
         * ```js
         * scanner.confidence = 50;
         * ```
         */
        confidence: number;
        /**
         * The time interval after the result is found in once reading and before the decoding begins in next reading. Default 100(ms).
         * ```js
         * scanner.intervalTime = 100;//100ms
         * ```
         */
        intervalTime: number;
        /**
         * Defines the different settings of the barcode reader itself. Find a full list of these settings and their corresponding descriptions [here](https://www.dynamsoft.com/help/Barcode-Reader/devguide/Template/TemplateSettingsList.html).<br>
         * The default setting is for an environment with accurate focus and good lighting. The settings below are for more complex environments.<br>
         * ```js
         * scanner.runtimeSettings.mAntiDamageLevel = 9;
         * scanner.runtimeSettings.mDeblurLevel = 9;
         * ```
         */
        runtimeSettings: RuntimeSettings;
        /**
         * Intercept an area and resize for decoding.
         * @see [drawImage](https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D/drawImage).
         * ```js
         * // Intercept the area of 200*200 in the upper left corner for decoding.
         * scanner.searchRegion = {sx:0, sy:0, sWidth:200, sHeight:200, dWidth:200, dHeight:200};
         * // Intercept the central 60%*60% area for decoding.
         * scanner.searchRegion = {sx: 0.2, sy: 0.2, sWidth: 0.6, sHeight: 0.6, dWidth: 0.6, dHeight: 0.6};
         * // Intercept the central 60%*60% area and resize to 1280*720 for decoding.
         * scanner.searchRegion = {sx: 0.25, sy: 0.25, sWidth: 0.5, sHeight: 0.5, dWidth: 1280, dHeight: 720}
         * ```
         */
        searchRegion: ScannerSearchRegion;
        /**
         * Whether show oriVideoCanvas in result.
         * ```js
         * scanner.bAddOriVideoCanvasToResult = true;
         * ```
         */
        bAddOriVideoCanvasToResult: boolean;
        /**
         * Whether show searchRegionCanvas in result.
         * ```js
         * scanner.bAddSearchRegionCanvasToResult = true;
         * ```
         */
        bAddSearchRegionCanvasToResult: boolean;
        /**
         * The event that is triggered once a single frame has been scanned. The results object contains all the barcode results that the reader was able to decode.
         * ```js
         * // Draw the barcodes area.
         * scanner.onFrameRead = results => {
         *   // Reset the width and height and empty the canvas
         *   canvas.width = video.videoWidth;
         *   canvas.height = video.videoHeight;
         *   let ctx = canvas.getContext('2d');
         *   // Set color
         *   ctx.fillStyle = 'rgba(254,180,32,0.3)';
         *   for(var result of results){
         *     // Get localization
         *     let x1 = result.LocalizationResult.X1;
         *     let x2 = result.LocalizationResult.X2;
         *     let x3 = result.LocalizationResult.X3;
         *     let x4 = result.LocalizationResult.X4;
         *     let y1 = result.LocalizationResult.Y1;
         *     let y2 = result.LocalizationResult.Y2;
         *     let y3 = result.LocalizationResult.Y3;
         *     let y4 = result.LocalizationResult.Y4;
         *     // Draw
         *     ctx.beginPath();
         *     ctx.moveTo(x1, y1);
         *     ctx.lineTo(x2, y2);
         *     ctx.lineTo(x3, y3);
         *     ctx.lineTo(x4, y4);
         *     ctx.fill();
         *   }
         * };
         * ```
         */
        onFrameRead?: (results: TextResult[]) => void;
        /**
         * The amount of time the reader "remembers" a barcode result once a single frame is read. Once the barcode result is obtained, the scanner will not trigger `onNewCodeRead` for the specific barcode again until forgetTime is up.
         * ```js
         * scanner.duplicateForgetTime = 15000; // 15s
         * ```
         */
        duplicateForgetTime: number;
        /**
         * This event is triggered when a not duplicated new barcode is found. `txt` holds the barcode text result. `result` contains the actual barcode result, including the text result. Old barcode will remember for `duplicateForgetTime`.
         * ```js
         * scanner.onNewCodeRead = txt => { alert(txt); }
         * ```
         */
        onNewCodeRead?: (txt: string, result: TextResult) => void;
        /**
         * Start the video and read barcodes.
         * ```js
         * scanner.open().then(info => {
         *   console.log(info.width + 'x' + info.height);
         *   console.log(info.current);
         *   console.log(info.all);
         * });
         * ```
         */
        open(): Promise<ScannerOpenCallbackInfo>;
        /**
         * Change video settings during reading.
         * ```js
         * scanner.play(undefined, 1280, 720).then(info => {
         *   console.log(info.width + 'x' + info.height);
         * });
         * ```
         * @param deviceId 
         * @param width 
         * @param height 
         */
        play(deviceId?: string, width?: number, height?: number): Promise<ScannerPlayCallbackInfo>;
        /**
         * Pause the video.
         * ```js
         * scanner.pause();
         * ```
         */
        pause(): void;
        /**
         * Close the video.
         * ```js
         * scanner.close();
         * ```
         */
        close(): void;
        /**
         * Update device list
         * ```js
         * updateDevice.open().then(info => {
         *   console.log(info.current);
         *   console.log(info.all);
         * });
         * ```
         */
        updateDevice(): Promise<ScannerUpdateDeviceCallbackInfo>;
    }
    
    /**
     * @see [[Scanner]]
     */
    interface ScannerConfig{
        /**
         * The HTML element that will contain the scanner object should you choose to customize the UI. It will have a simple default UI if you keep `htmlElement` undefined in initialization config.
         * ```js
         * scanner.htmlElement.appendChild(aDiv);
         * // Warning: Avoid changing the binding if the scanner is already open.
         * scanner.htmlElement = aVideoContainer;
         * ```
         */
        htmlElement?: HTMLElement;
        /**
         * Video play settings.
         * Refer [MediaStreamConstraints](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia#Syntax).
         * If during reading, need play again to make it effective.
         * ```js
         * scanner.videoSettings = { video: { width: 1280, height: 720, facingMode: "environment" } };
         * ```
         */
        videoSettings?: MediaStreamConstraints;
        /**
         * This property is mainly related to 1D barcodes.
         * If the confidence of a 1D barcode result is greater than or equal to this `confidence`, that is a reliable result that might cause `onNewCodeRead`.
         * Otherwise if the confidence of a result is smaller than this `confidence`, the result will be ignored by `onNewCodeRead`.
         * Default 30.
         * ```js
         * scanner.confidence = 50;
         * ```
         */
        confidence?: number;
        /**
         * The time interval after the result is found in once reading and before the decoding begins in next reading. Default 100(ms).
         * ```js
         * scanner.intervalTime = 100;//100ms
         * ```
         */
        intervalTime?: number;
        /**
         * Defines the different settings of the barcode reader itself. Find a full list of these settings and their corresponding descriptions [here](https://www.dynamsoft.com/help/Barcode-Reader/devguide/Template/TemplateSettingsList.html).<br>
         * The default setting is for an environment with accurate focus and good lighting. The settings below are for more complex environments.<br>
         * ```js
         * scanner.runtimeSettings.mAntiDamageLevel = 9;
         * scanner.runtimeSettings.mDeblurLevel = 9;
         * ```
         */
        runtimeSettings?: RuntimeSettingsForScannerConfig;
        /**
         * Intercept an area and resize for decoding.
         * @see [drawImage](https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D/drawImage).
         * ```js
         * // Intercept the area of 200*200 in the upper left corner for decoding.
         * scanner.searchRegion = {sx:0, sy:0, sWidth:200, sHeight:200, dWidth:200, dHeight:200};
         * // Intercept the central 60%*60% area for decoding.
         * scanner.searchRegion = {sx: 0.2, sy: 0.2, sWidth: 0.6, sHeight: 0.6, dWidth: 0.6, dHeight: 0.6};
         * // Intercept the central 60%*60% area and resize to 1280*720 for decoding.
         * scanner.searchRegion = {sx: 0.25, sy: 0.25, sWidth: 0.5, sHeight: 0.5, dWidth: 1280, dHeight: 720}
         * ```
         */
        searchRegion?: ScannerSearchRegion;
        /**
         * Whether show oriVideoCanvas in result.
         * ```js
         * scanner.bAddOriVideoCanvasToResult = true;
         * ```
         */
        bAddOriVideoCanvasToResult?: boolean;
        /**
         * Whether show searchRegionCanvas in result.
         * ```js
         * scanner.bAddSearchRegionCanvasToResult = true;
         * ```
         */
        bAddSearchRegionCanvasToResult?: boolean;
        /**
         * The event that is triggered once a single frame has been scanned. The results object contains all the barcode results that the reader was able to decode.
         * ```js
         * // Draw the barcodes area.
         * scanner.onFrameRead = results => {
         *   // Reset the width and height and empty the canvas
         *   canvas.width = video.videoWidth;
         *   canvas.height = video.videoHeight;
         *   let ctx = canvas.getContext('2d');
         *   // Set color
         *   ctx.fillStyle = 'rgba(254,180,32,0.3)';
         *   for(var result of results){
         *     // Get localization
         *     let x1 = result.LocalizationResult.X1;
         *     let x2 = result.LocalizationResult.X2;
         *     let x3 = result.LocalizationResult.X3;
         *     let x4 = result.LocalizationResult.X4;
         *     let y1 = result.LocalizationResult.Y1;
         *     let y2 = result.LocalizationResult.Y2;
         *     let y3 = result.LocalizationResult.Y3;
         *     let y4 = result.LocalizationResult.Y4;
         *     // Draw
         *     ctx.beginPath();
         *     ctx.moveTo(x1, y1);
         *     ctx.lineTo(x2, y2);
         *     ctx.lineTo(x3, y3);
         *     ctx.lineTo(x4, y4);
         *     ctx.fill();
         *   }
         * };
         * ```
         */
        onFrameRead?: (results: TextResult[]) => void;
        /**
         * The amount of time the reader "remembers" a barcode result once a single frame is read. Once the barcode result is obtained, the scanner will not trigger `onNewCodeRead` for the specific barcode again until forgetTime is up.
         * ```js
         * scanner.duplicateForgetTime = 15000; // 15s
         * ```
         */
        duplicateForgetTime?: number;
        /**
         * This event is triggered when a not duplicated new barcode is found. `txt` holds the barcode text result. `result` contains the actual barcode result, including the text result. Old barcode will remember for `duplicateForgetTime`.
         * ```js
         * scanner.onNewCodeRead = txt => { alert(txt); }
         * ```
         */
        onNewCodeRead?: (txt: string, result: TextResult) => void;
    }

    /**
     * @see [[RuntimeSettings]]
     */
    interface RuntimeSettingsForScannerConfig{
        /**
         * The degree of anti-damage of the barcode. This value decides how many localization algorithms will be used. To ensure the best results, the value of AntiDamageLevel is suggested to be set to 9 if the ExpectedBarcodesCount is set to 0 or 1; otherwise, the value of AntiDamageLevel is suggested to be set to 7.
         */
        mAntiDamageLevel?: number;
        /**
         * The types of barcode to be read. Barcode types can be combined as an array. For example, if you want to choose Code_39 and Code_93, you can set it to `EnumBarcodeFormat.CODE_39 | EnumBarcodeFormat.CODE_93`.
         */
        mBarcodeFormatIds?: number | EnumBarcodeFormat;
        /**
         * The ink colour for barcodes search.
         */
        mBarcodeInvertMode?: number;
        /**
         * The block size for the process of binarization. Block size means the size of a pixel neighbourhood that is used to calculate a threshold value for the pixel.
         */
        mBinarizationBlockSize?: number;
        /**
         * Whether to convert colour images. Recommend setting it to "Auto" if you want to pre-detect the barcode regions.
         */
        mColourImageConvertMode?: number;
        /**
         * The degree of blurriness of the barcode. The higher value you set, the much more effort the library will take to decode images, but it may also slow down the recognition process.
         */
        mDeblurLevel?: number;
        /**
         * For barcodes with a large module size there might be a vacant area in the position detection pattern after binarization which may result in a decoding failure. Setting this to true will fill in the vacant area with black and may help to decode it successfully.
         */
        mEnableFillBinaryVacancy?: number;
        /**
         * The expected number of barcodes to read for each image (or each region of the image if you specified barcode regions).
         */
        mExpectedBarcodesCount?: number;
        /**
         * The sensitivity used for gray equalization. The higher the value, the more likely gray equalization will be activated. Effective for images with low comparison between black and white colour. May cause adverse effect on images with high level of black and white colour comparison.
         */
        mGrayEqualizationSensitivity?: number;
        /**
         * The priority of localization algorithms.
         */
        mLocalizationAlgorithmPriority?: string;
        /**
         * The amount of image processing algorithm threads used to decode barcodes.
         */
        mMaxAlgorithmThreadCount?: number;
        /**
         * The maximum number of barcodes to read.
         */
        mMaxBarcodesCount?: number;
        /**
         * The maximum dimension of full image as barcode zone. Sets the maximum image dimension (in pixels) to localize barcode on the full image. If the image dimension is smaller than the given value, the library will localize barcode on the full image. Otherwise, "FullImageAsBarcodeZone" mode will not be enabled.
         */
        mMaxImageDimensionToLocalizeBarcodesOnFullImage?: number;
        /**
         * The output image resolution. When you are trying to decode a PDF file using DecodeFile method, the library will convert the pdf file to image(s) first, then perform barcode recognition.
         */
        mPDFRasterDPI?: number;
        /**
         * Values that represent region predetection modes
         */
        mRegionPredetectionMode?: number;
        /**
         * Reserved memory for struct. The length of this array indicates the size of the memory reserved for this struct.
         */
        mReserved?: string;
        /**
         * The threshold value of the image shrinking. If the shorter edge size is larger than the given value, the library will calculate the required height and width of the barcode image and shrink the image to that size before localization. Otherwise, it will perform barcode localization on the original image.
         */
        mScaleDownThreshold?: number;
        /**
         * The text filter mode for barcodes search.
         */
        mTextFilterMode?: number;
        /**
         * The sensitivity for texture detection. The higher value you set, the more efforts it will take to detect texture.
         */
        mTextureDetectionSensitivity?: number;
        /**
         * The maximum amount of time (in milliseconds) it should spend searching for a barcode per page. It does not include the time taken to load/decode an image (Tiff, PNG, etc) from disk into memory.
         */
        mTimeout?: number;
    }
    
    interface VideoDeviceInfo{
        deviceId: string;
        label: string;
    }
    
    interface ScannerSearchRegion{
        sx?: number;
        sy?: number;
        sWidth?: number;
        sHeight?: number;
        dWidth?: number;
        dHeight?: number;
    }
    
    interface ScannerPlayCallbackInfo{
        width: number,
        height: number
    }
    interface ScannerUpdateDeviceCallbackInfo{
        current?: VideoDeviceInfo,
        all: VideoDeviceInfo[]
    }
        
    interface ScannerOpenCallbackInfo extends ScannerPlayCallbackInfo, ScannerUpdateDeviceCallbackInfo {
    }

    enum EnumBarcodeFormat{
        All = 0x1e0003ff,
        OneD = 0x3FF,
        CODE_39 = 0x1,
        CODE_128 = 0x2,
        CODE_93 = 0x4,
        CODABAR = 0x8,
        ITF = 0x10,
        EAN_13 = 0x20,
        EAN_8 = 0x40,
        UPC_A = 0x80,
        UPC_E = 0x100,
        INDUSTRIAL_25 = 0x200,
        PDF417 = 0x2000000,
        QR_CODE = 0x4000000,
        DATAMATRIX = 0x8000000,
        AZTEC = 0x10000000
    }
    enum EnumErrorCode{
        DBR_SYSTEM_EXCEPTION = 1,
        DBR_SUCCESS = 0,
        DBR_UNKNOWN = -10000,
        DBR_NO_MEMORY = -10001,
        DBR_NULL_REFERENCE = -10002,
        DBR_LICENSE_INVALID = -10003,
        DBR_LICENSE_EXPIRED = -10004,
        DBR_FILE_NOT_FOUND = -10005,
        DBR_FILETYPE_NOT_SUPPORTED = -10006,
        DBR_BPP_NOT_SUPPORTED = -10007,
        DBR_INDEX_INVALID = -10008,
        DBR_BARCODE_FORMAT_INVALID = -10009,
        DBR_CUSTOM_REGION_INVALID = -10010,
        DBR_MAX_BARCODE_NUMBER_INVALID = -10011,
        DBR_IMAGE_READ_FAILED = -10012,
        DBR_TIFF_READ_FAILED = -10013,
        DBR_QR_LICENSE_INVALID = -10016,
        DBR_1D_LICENSE_INVALID = -10017,
        DBR_DIB_BUFFER_INVALID = -10018,
        DBR_PDF417_LICENSE_INVALID = -10019,
        DBR_DATAMATRIX_LICENSE_INVALID = -10020,
        DBR_PDF_READ_FAILED = -10021,
        DBR_PDF_DLL_MISSING = -10022,
        DBR_PAGE_NUMBER_INVALID = -10023,
        DBR_CUSTOM_SIZE_INVALID = -10024,
        DBR_CUSTOM_MODULESIZE_INVALID = -10025,
        DBR_RECOGNITION_TIMEOUT = -10026,
        DBR_JSON_PARSE_FAILED = -10030,
        DBR_JSON_TYPE_INVALID = -10031,
        DBR_JSON_KEY_INVALID = -10032,
        DBR_JSON_VALUE_INVALID = -10033,
        DBR_JSON_NAME_KEY_MISSING = -10034,
        DBR_JSON_NAME_VALUE_DUPLICATED = -10035,
        DBR_TEMPLATE_NAME_INVALID = -10036,
        DBR_JSON_NAME_REFERENCE_INVALID = -10037,
        DBR_PARAMETER_VALUE_INVALID = -10038,
        DBR_DOMAIN_NOT_MATCHED = -10039,
        DBR_RESERVEDINFO_NOT_MATCHED = -10040,
        DBR_DBRERR_AZTEC_LICENSE_INVALID = -10041
    }
    enum EnumImagePixelFormat{
        IPF_Binary = 0,
        IPF_BinaryInverted = 1,
        IPF_GrayScaled = 2,
        IPF_NV21 = 3,
        IPF_RGB_565 = 4,
        IPF_RGB_555 = 5,
        IPF_RGB_888 = 6,
        IPF_ARGB_8888 = 7,
        IPF_RGB_161616 = 8,
        IPF_ARGB_16161616 = 9
    }
    enum EnumResultType{
        EDT_StandardText = 0,
        EDT_RawText = 1,
        EDT_CandidateText = 2,
        EDT_PartialText = 3
    }
    enum EnumTerminateStage{
        ETS_Prelocalized = 0,
        ETS_Localized = 1,
        ETS_Recognized = 2
    }
    enum EnumConflictMode{
        ECM_Ignore = 1,
        ECM_Overwrite = 2
    }
    class BarcodeReaderException extends Error{
        code?: EnumErrorCode;
    }
}

type dbr = BarcodeReader;
declare namespace dynamsoft{
    type BarcodeReader = dbr;
}

export default BarcodeReader;