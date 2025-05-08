<?php
define('FPDF_VERSION','1.84');

class FPDF
{
protected $page;               // current page number
protected $n;                  // current object number
protected $offsets;           // array of object offsets
protected $buffer;            // buffer holding in-memory PDF
protected $pages;             // array containing pages
protected $state;             // current document state
protected $compress;          // compression flag
protected $k;                 // scale factor (number of points in user unit)
protected $DefOrientation;    // default orientation
protected $CurOrientation;    // current orientation
protected $StdPageSizes;      // standard page sizes
protected $DefPageSize;       // default page size
protected $CurPageSize;       // current page size
protected $CurRotation;       // current page rotation
protected $PageInfo;          // page-related data
protected $wPt, $hPt;         // dimensions of current page in points
protected $w, $h;             // dimensions of current page in user unit
protected $lMargin;           // left margin
protected $tMargin;           // top margin
protected $rMargin;           // right margin
protected $bMargin;           // page break margin
protected $cMargin;           // cell margin
protected $x, $y;             // current position in user unit
protected $lasth;             // height of last printed cell
protected $LineWidth;         // line width in user unit
protected $fontpath;          // path containing fonts
protected $CoreFonts;         // array of core font names
protected $fonts;             // array of used fonts
protected $FontFiles;         // array of font files
protected $encodings;         // array of encodings
protected $cmaps;             // array of ToUnicode CMaps
protected $FontFamily;        // current font family
protected $FontStyle;         // current font style
protected $underline;         // underlining flag
protected $CurrentFont;       // current font info
protected $FontSizePt;        // current font size in points
protected $FontSize;          // current font size in user unit
protected $DrawColor;         // commands for drawing color
protected $FillColor;         // commands for filling color
protected $TextColor;         // commands for text color
protected $ColorFlag;         // indicates whether fill and text colors are different
protected $WithAlpha;         // indicates whether alpha channel is used
protected $ws;                // word spacing
protected $images;            // array of used images
protected $PageLinks;         // array of links in pages
protected $links;             // array of internal links
protected $AutoPageBreak;     // automatic page breaking
protected $PageBreakTrigger;  // threshold used to trigger page breaks
protected $InHeader;          // flag set when processing header
protected $InFooter;          // flag set when processing footer
protected $AliasNbPages;      // alias for total number of pages
protected $ZoomMode;          // zoom display mode
protected $LayoutMode;        // layout display mode
protected $title;             // title
protected $subject;           // subject
protected $author;            // author
protected $keywords;          // keywords
protected $creator;           // creator
protected $PDFVersion;        // PDF version number
protected $diffs;             // Array of encoding differences
protected $winansi;           // Current font encoding

function __construct($orientation='P', $unit='mm', $size='A4')
{
    // Some checks
    $this->_dochecks();
    // Initialize PDF document
    $this->page = 0;
    $this->n = 2;
    $this->buffer = '';
    $this->pages = array();
    $this->PageInfo = array();
    $this->state = 0;
    $this->fonts = array();
    $this->FontFiles = array();
    $this->encodings = array();
    $this->cmaps = array();
    $this->images = array();
    $this->links = array();
    $this->InHeader = false;
    $this->InFooter = false;
    $this->lasth = 0;
    $this->FontFamily = '';
    $this->FontStyle = '';
    $this->FontSizePt = 12;
    $this->underline = false;
    $this->DrawColor = '0 G';
    $this->FillColor = '0 g';
    $this->TextColor = '0 g';
    $this->ColorFlag = false;
    $this->WithAlpha = false;
    $this->ws = 0;
    $this->diffs = array();
    $this->winansi = false;
    
    // Font path
    if(defined('FPDF_FONTPATH'))
        $this->fontpath = FPDF_FONTPATH;
    else
        $this->fontpath = dirname(__FILE__).'/font/';
    
    // Core fonts
    $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
    
    // Scale factor
    if($unit=='pt')
        $this->k = 1;
    elseif($unit=='mm')
        $this->k = 72/25.4;
    elseif($unit=='cm')
        $this->k = 72/2.54;
    elseif($unit=='in')
        $this->k = 72;
    else
        $this->Error('Incorrect unit: '.$unit);
    
    // Page sizes
    $this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
        'letter'=>array(612,792), 'legal'=>array(612,1008));
    $size = $this->_getpagesize($size);
    $this->DefPageSize = $size;
    $this->CurPageSize = $size;
    
    // Page orientation
    $orientation = strtolower($orientation);
    if($orientation=='p' || $orientation=='portrait')
    {
        $this->DefOrientation = 'P';
        $this->w = $size[0];
        $this->h = $size[1];
    }
    elseif($orientation=='l' || $orientation=='landscape')
    {
        $this->DefOrientation = 'L';
        $this->w = $size[1];
        $this->h = $size[0];
    }
    else
        $this->Error('Incorrect orientation: '.$orientation);
    $this->CurOrientation = $this->DefOrientation;
    $this->wPt = $this->w*$this->k;
    $this->hPt = $this->h*$this->k;
    
    // Page rotation
    $this->CurRotation = 0;
    
    // Page margins (1 cm)
    $margin = 28.35/$this->k;
    $this->SetMargins($margin,$margin);
    
    // Interior cell margin (1 mm)
    $this->cMargin = $margin/10;
    
    // Line width (0.2 mm)
    $this->LineWidth = .567/$this->k;
    
    // Automatic page break
    $this->SetAutoPageBreak(true,2*$margin);
    
    // Default display mode
    $this->SetDisplayMode('default');
    
    // Enable compression
    $this->SetCompression(true);
    
    // Set default font
    $this->SetFont('helvetica');
}

protected function _dochecks()
{
    // Check availability of mbstring
    if(!function_exists('mb_strlen'))
        $this->Error('mbstring extension is required for FPDF');
}

protected function _getpagesize($size)
{
    if(is_string($size))
    {
        $size = strtolower($size);
        if(!isset($this->StdPageSizes[$size]))
            $this->Error('Unknown page size: '.$size);
        $a = $this->StdPageSizes[$size];
        return array($a[0]/$this->k, $a[1]/$this->k);
    }
    else
    {
        if($size[0]>$size[1])
            return array($size[1], $size[0]);
        else
            return $size;
    }
}

public function SetMargins($left, $top, $right=null)
{
    // Set left, top and right margins
    $this->lMargin = $left;
    $this->tMargin = $top;
    if($right===null)
        $this->rMargin = $left;
    else
        $this->rMargin = $right;
}

public function SetAutoPageBreak($auto, $margin=0)
{
    // Set auto page break mode and triggering margin
    $this->AutoPageBreak = $auto;
    $this->bMargin = $margin;
    $this->PageBreakTrigger = $this->h-$margin;
}

public function SetDisplayMode($zoom, $layout='default')
{
    // Set display mode in viewer
    if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
        $this->ZoomMode = $zoom;
    else
        $this->Error('Incorrect zoom display mode: '.$zoom);
    if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
        $this->LayoutMode = $layout;
    else
        $this->Error('Incorrect layout display mode: '.$layout);
}

public function SetCompression($compress)
{
    // Set page compression
    if(function_exists('gzcompress'))
        $this->compress = $compress;
    else
        $this->compress = false;
}

public function Output($dest='', $name='', $isUTF8=false)
{
    // Output PDF to some destination
    $this->Close();
    if(strlen($name)==1 && strlen($dest)!=1)
    {
        // Fix parameter order
        $tmp = $dest;
        $dest = $name;
        $name = $tmp;
    }
    if($dest=='')
        $dest = 'I';
    if($name=='')
        $name = 'doc.pdf';
    switch(strtoupper($dest))
    {
        case 'I':
            // Send to standard output
            $this->_checkoutput();
            if(PHP_SAPI!='cli')
            {
                // We send to a browser
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; '.$this->_httpencode('filename',$name,$isUTF8));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
            }
            echo $this->buffer;
            break;
        case 'D':
            // Download file
            $this->_checkoutput();
            header('Content-Type: application/x-download');
            header('Content-Disposition: attachment; '.$this->_httpencode('filename',$name,$isUTF8));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $this->buffer;
            break;
        case 'F':
            // Save to local file
            if(!file_put_contents($name,$this->buffer))
                $this->Error('Unable to create output file: '.$name);
            break;
        case 'S':
            // Return as a string
            return $this->buffer;
        default:
            $this->Error('Incorrect output destination: '.$dest);
    }
    return '';
}

protected function _checkoutput()
{
    if(PHP_SAPI!='cli')
    {
        if(headers_sent($file,$line))
            $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
    }
    if(ob_get_length())
    {
        // The output buffer is not empty
        if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
        {
            // It contains only a UTF-8 BOM and/or whitespace, let's clean it
            ob_clean();
        }
        else
            $this->Error("Some data has already been output, can't send PDF file");
    }
}

protected function _httpencode($param, $value, $isUTF8)
{
    // Encode HTTP header field parameter
    if($this->_isascii($value))
        return $param.'="'.$value.'"';
    if(!$isUTF8)
        $value = utf8_encode($value);
    if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false)
        return $param.'="'.rawurlencode($value).'"';
    else
        return $param."*=UTF-8''".rawurlencode($value);
}

protected function _isascii($s)
{
    // Test if string is ASCII
    $nb = strlen($s);
    for($i=0;$i<$nb;$i++)
    {
        if(ord($s[$i])>127)
            return false;
    }
    return true;
}

protected function Error($msg)
{
    // Fatal error
    throw new Exception('FPDF error: '.$msg);
}

public function AddPage($orientation='', $size='', $rotation=0)
{
    // Start a new page
    if($this->state==3)
        $this->Error('The document is closed');
    $family = $this->FontFamily;
    $style = $this->FontStyle.($this->underline ? 'U' : '');
    $fontsize = $this->FontSizePt;
    $lw = $this->LineWidth;
    $dc = $this->DrawColor;
    $fc = $this->FillColor;
    $tc = $this->TextColor;
    $cf = $this->ColorFlag;
    if($this->page>0)
    {
        // Page footer
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
    }
    // Start new page
    $this->_beginpage($orientation,$size,$rotation);
    // Set line cap style to square
    $this->_out('2 J');
    // Set line width
    $this->LineWidth = $lw;
    $this->_out(sprintf('%.2F w',$lw*$this->k));
    // Set font
    if($family)
        $this->SetFont($family,$style,$fontsize);
    // Set colors
    $this->DrawColor = $dc;
    if($dc!='0 G')
        $this->_out($dc);
    $this->FillColor = $fc;
    if($fc!='0 g')
        $this->_out($fc);
    $this->TextColor = $tc;
    $this->ColorFlag = $cf;
    // Page header
    $this->InHeader = true;
    $this->Header();
    $this->InHeader = false;
    // Restore line width
    if($this->LineWidth!=$lw)
    {
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w',$lw*$this->k));
    }
    // Restore font
    if($family)
        $this->SetFont($family,$style,$fontsize);
    // Restore colors
    if($this->DrawColor!=$dc)
    {
        $this->DrawColor = $dc;
        $this->_out($dc);
    }
    if($this->FillColor!=$fc)
    {
        $this->FillColor = $fc;
        $this->_out($fc);
    }
    $this->TextColor = $tc;
    $this->ColorFlag = $cf;
}

public function Header()
{
    // To be implemented in your inherited class
}

public function Footer()
{
    // To be implemented in your inherited class
}

public function SetFont($family, $style='', $size=0)
{
    // Select a font; size given in points
    if($family=='')
        $family = $this->FontFamily;
    else
        $family = strtolower($family);
    $style = strtoupper($style);
    if(strpos($style,'U')!==false)
    {
        $this->underline = true;
        $style = str_replace('U','',$style);
    }
    else
        $this->underline = false;
    if($style=='IB')
        $style = 'BI';
    if($size==0)
        $size = $this->FontSizePt;
    // Test if font is already selected
    if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
        return;
    // Test if font is already loaded
    $fontkey = $family.$style;
    if(!isset($this->fonts[$fontkey]))
    {
        // Test if one of the core fonts
        if($family=='arial')
            $family = 'helvetica';
        if(in_array($family,$this->CoreFonts))
        {
            if($family=='symbol' || $family=='zapfdingbats')
                $style = '';
            $fontkey = $family.$style;
            if(!isset($this->fonts[$fontkey]))
                $this->_loadfont($family);
        }
        else
            $this->Error('Undefined font: '.$family.' '.$style);
    }
    // Select it
    $this->FontFamily = $family;
    $this->FontStyle = $style;
    $this->FontSizePt = $size;
    $this->FontSize = $size/$this->k;
    $this->CurrentFont = &$this->fonts[$fontkey];
    if($this->page>0)
        $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
    // Output a cell
    $k = $this->k;
    if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
    {
        // Automatic page break
        $x = $this->x;
        $ws = $this->ws;
        if($ws>0)
        {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        $this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
        $this->x = $x;
        if($ws>0)
        {
            $this->ws = $ws;
            $this->_out(sprintf('%.3F Tw',$ws*$k));
        }
    }
    if($w==0)
        $w = $this->w-$this->rMargin-$this->x;
    $s = '';
    if($fill || $border==1)
    {
        if($fill)
            $op = ($border==1) ? 'B' : 'f';
        else
            $op = 'S';
        $s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    }
    if(is_string($border))
    {
        $x = $this->x;
        $y = $this->y;
        if(strpos($border,'L')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'T')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        if(strpos($border,'R')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'B')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    }
    if($txt!=='')
    {
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        if($align=='R')
            $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
        elseif($align=='C')
            $dx = ($w-$this->GetStringWidth($txt))/2;
        else
            $dx = $this->cMargin;
        if($this->ColorFlag)
            $s .= 'q '.$this->TextColor.' ';
        $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
        if($this->underline)
            $s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
        if($this->ColorFlag)
            $s .= ' Q';
        if($link)
            $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
    }
    if($s)
        $this->_out($s);
    $this->lasth = $h;
    if($ln>0)
    {
        // Go to next line
        $this->y += $h;
        if($ln==1)
            $this->x = $this->lMargin;
    }
    else
        $this->x += $w;
}

public function Ln($h=null)
{
    // Line feed; default value is the last cell height
    $this->x = $this->lMargin;
    if($h===null)
        $this->y += $this->lasth;
    else
        $this->y += $h;
}

protected function _loadfont($font)
{
    // Load a font definition file
    include($this->fontpath.$font.'.php');
    $this->fonts[$font] = array('i'=>count($this->fonts)+1);
    if(!empty($diff))
    {
        // Search existing encodings
        $d = 0;
        $nb = count($this->diffs);
        for($i=1;$i<=$nb;$i++)
        {
            if($this->diffs[$i]==$diff)
            {
                $d = $i;
                break;
            }
        }
        if($d==0)
        {
            $d = $nb+1;
            $this->diffs[$d] = $diff;
        }
        $this->fonts[$font]['diff'] = $d;
    }
    if(!empty($enc))
        $this->fonts[$font]['enc'] = $enc;
    if(!empty($file))
    {
        if($type=='TrueType')
            $this->FontFiles[$file] = array('length1'=>$originalsize);
        else
            $this->FontFiles[$file] = array('length1'=>$size1, 'length2'=>$size2);
    }
}

protected function _escape($s)
{
    // Escape special characters
    if(strpos($s,'(')!==false || strpos($s,')')!==false || strpos($s,'\\')!==false || strpos($s,"\r")!==false)
        return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s);
    else
        return $s;
}

protected function _beginpage($orientation='', $size='', $rotation=0)
{
    $this->page++;
    $this->pages[$this->page] = '';
    $this->state = 2;
    $this->x = $this->lMargin;
    $this->y = $this->tMargin;
    $this->FontFamily = '';
    // Check page size and orientation
    if($orientation=='')
        $orientation = $this->DefOrientation;
    else
        $orientation = strtoupper($orientation[0]);
    if($size=='')
        $size = $this->DefPageSize;
    else
        $size = $this->_getpagesize($size);
    if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
    {
        // New size or orientation
        if($orientation=='P')
        {
            $this->w = $size[0];
            $this->h = $size[1];
        }
        else
        {
            $this->w = $size[1];
            $this->h = $size[0];
        }
        $this->wPt = $this->w*$this->k;
        $this->hPt = $this->h*$this->k;
        $this->PageBreakTrigger = $this->h-$this->bMargin;
        $this->CurOrientation = $orientation;
        $this->CurPageSize = $size;
    }
    if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
        $this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
    if($rotation!=0)
    {
        if($rotation%90!=0)
            $this->Error('Incorrect rotation value: '.$rotation);
        $this->CurRotation = $rotation;
        $this->PageInfo[$this->page]['rotation'] = $rotation;
    }
}

protected function _out($s)
{
    // Add a line to the document
    if($this->state==2)
        $this->pages[$this->page] .= $s."\n";
    elseif($this->state==1)
        $this->_put($s);
    elseif($this->state==0)
        $this->Error('No page has been added yet');
    elseif($this->state==3)
        $this->Error('The document is closed');
}

protected function _put($s)
{
    $this->buffer .= $s."\n";
}

public function GetStringWidth($s)
{
    // Get width of a string in the current font
    $s = (string)$s;
    $cw = &$this->CurrentFont['cw'];
    $w = 0;
    $l = strlen($s);
    for($i=0;$i<$l;$i++)
        $w += $cw[$s[$i]];
    return $w*$this->FontSize/1000;
}

protected function AcceptPageBreak()
{
    // Accept automatic page break or not
    return $this->AutoPageBreak;
}

public function Close()
{
    // Terminate document
    if($this->state==3)
        return;
    if($this->page==0)
        $this->AddPage();
    // Page footer
    $this->InFooter = true;
    $this->Footer();
    $this->InFooter = false;
    // Close page
    $this->_endpage();
    // Close document
    $this->_enddoc();
}

protected function _endpage()
{
    $this->state = 1;
}

protected function _enddoc()
{
    $this->state = 3;
}

protected function _dounderline($x, $y, $txt)
{
    // Get width of string
    $w = $this->GetStringWidth($txt);
    
    return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y+.5/72))*$this->k,$w*$this->k,-1/$this->k);
}

protected function _putfonts()
{
    foreach($this->FontFiles as $file=>$info)
    {
        // Font file embedding
        $this->_newobj();
        $this->FontFiles[$file]['n'] = $this->n;
        $font = file_get_contents($this->fontpath.$file);
        if(!$font)
            $this->Error('Font file not found: '.$file);
        $this->_put('<</Length '.strlen($font));
        if(substr($file,-2)=='.z')
            $this->_put('/Filter /FlateDecode');
        $this->_put('/Length1 '.$info['length1']);
        if(isset($info['length2']))
            $this->_put('/Length2 '.$info['length2'].' /Length3 0');
        $this->_put('>>');
        $this->_putstream($font);
        $this->_put('endobj');
    }
    foreach($this->fonts as $k=>$font)
    {
        // Encoding
        if(isset($font['diff']))
        {
            if(!isset($this->fonts[$k]['n']))
                $this->_newobj();
            $this->_put('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$font['diff'].']>>');
            $this->_put('endobj');
        }
        // Font file
        if(isset($font['file']))
        {
            if(!isset($this->fonts[$k]['n']))
                $this->_newobj();
            $this->_put('<</Type /Font');
            $this->_put('/BaseFont /'.$font['name']);
            if($font['type']=='Type1')
            {
                $this->_put('/Subtype /Type1');
                if($font['enc'])
                    $this->_put('/Encoding /'.$font['enc']);
            }
            else
            {
                $this->_put('/Subtype /TrueType');
                $this->_put('/FirstChar 32');
                $this->_put('/LastChar 255');
                $this->_put('/Widths '.($this->n+1).' 0 R');
                $this->_put('/FontDescriptor '.($this->n+2).' 0 R');
                if($font['enc'])
                    $this->_put('/Encoding /'.$font['enc']);
            }
            $this->_put('>>');
            $this->_put('endobj');
        }
    }
}

protected function _putstream($data)
{
    $this->_put('stream');
    $this->_put($data);
    $this->_put('endstream');
}

protected function _newobj()
{
    $this->n++;
    $this->offsets[$this->n] = strlen($this->buffer);
    $this->_put($this->n.' 0 obj');
}
}
?> 