<?php

namespace RFC;


class StringUtils
{
    /**
     * Set the mbstring internal encoding to a binary safe encoding when func_overload
     * is enabled.
     *
     * When mbstring.func_overload is in use for multi-byte encodings, the results from
     * strlen() and similar functions respect the utf8 characters, causing binary data
     * to return incorrect lengths.
     *
     * This function overrides the mbstring encoding to a binary-safe encoding, and
     * resets it to the users expected encoding afterwards through the
     * `reset_mbstring_encoding` function.
     *
     * It is safe to recursively call this function, however each
     * `mbstring_binary_safe_encoding()` call must be followed up with an equal number
     * of `reset_mbstring_encoding()` calls.
     *
     * @since 3.7.0
     *
     * @see reset_mbstring_encoding()
     *
     * @staticvar array $encodings
     * @staticvar bool  $overloaded
     *
     * @param bool $reset Optional. Whether to reset the encoding back to a previously-set encoding.
     *                    Default false.
     */
    static function mbstring_binary_safe_encoding( $reset = false ) {
        static $encodings = array();
        static $overloaded = null;

        if ( is_null( $overloaded ) )
            $overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );

        if ( false === $overloaded )
            return;

        if ( ! $reset ) {
            $encoding = mb_internal_encoding();
            array_push( $encodings, $encoding );
            mb_internal_encoding( 'ISO-8859-1' );
        }

        if ( $reset && $encodings ) {
            $encoding = array_pop( $encodings );
            mb_internal_encoding( $encoding );
        }
    }

    /**
     * Reset the mbstring internal encoding to a users previously set encoding.
     *
     * @see mbstring_binary_safe_encoding()
     *
     * @since 3.7.0
     */
    static function reset_mbstring_encoding() {
        self::mbstring_binary_safe_encoding( true );
    }

    /**
     * Checks to see if a string is utf8 encoded.
     *
     * NOTE: This function checks for 5-Byte sequences, UTF8
     *       has Bytes Sequences with a maximum length of 4.
     *
     * @author bmorel at ssi dot fr (modified)
     * @since 1.2.1
     *
     * @param string $str The string to be checked
     * @return bool True if $str fits a UTF-8 model, false otherwise.
     */
    static function seems_utf8( $str ) {
        self::mbstring_binary_safe_encoding();
        $length = strlen($str);
        self::reset_mbstring_encoding();
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; // 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; // 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; // 1111110b
            else return false; // Does not match any model
            for ($j=0; $j<$n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * **Accent characters converted:**
     *
     * Currency signs:
     *
     * |   Code   | Glyph | Replacement |     Description     |
     * | -------- | ----- | ----------- | ------------------- |
     * | U+00A3   | ??     | (empty)     | British Pound sign  |
     * | U+20AC   | ???     | E           | Euro sign           |
     *
     * Decompositions for Latin-1 Supplement:
     *
     * |  Code   | Glyph | Replacement |               Description              |
     * | ------- | ----- | ----------- | -------------------------------------- |
     * | U+00AA  | ??     | a           | Feminine ordinal indicator             |
     * | U+00BA  | ??     | o           | Masculine ordinal indicator            |
     * | U+00C0  | ??     | A           | Latin capital letter A with grave      |
     * | U+00C1  | ??     | A           | Latin capital letter A with acute      |
     * | U+00C2  | ??     | A           | Latin capital letter A with circumflex |
     * | U+00C3  | ??     | A           | Latin capital letter A with tilde      |
     * | U+00C4  | ??     | A           | Latin capital letter A with diaeresis  |
     * | U+00C5  | ??     | A           | Latin capital letter A with ring above |
     * | U+00C6  | ??     | AE          | Latin capital letter AE                |
     * | U+00C7  | ??     | C           | Latin capital letter C with cedilla    |
     * | U+00C8  | ??     | E           | Latin capital letter E with grave      |
     * | U+00C9  | ??     | E           | Latin capital letter E with acute      |
     * | U+00CA  | ??     | E           | Latin capital letter E with circumflex |
     * | U+00CB  | ??     | E           | Latin capital letter E with diaeresis  |
     * | U+00CC  | ??     | I           | Latin capital letter I with grave      |
     * | U+00CD  | ??     | I           | Latin capital letter I with acute      |
     * | U+00CE  | ??     | I           | Latin capital letter I with circumflex |
     * | U+00CF  | ??     | I           | Latin capital letter I with diaeresis  |
     * | U+00D0  | ??     | D           | Latin capital letter Eth               |
     * | U+00D1  | ??     | N           | Latin capital letter N with tilde      |
     * | U+00D2  | ??     | O           | Latin capital letter O with grave      |
     * | U+00D3  | ??     | O           | Latin capital letter O with acute      |
     * | U+00D4  | ??     | O           | Latin capital letter O with circumflex |
     * | U+00D5  | ??     | O           | Latin capital letter O with tilde      |
     * | U+00D6  | ??     | O           | Latin capital letter O with diaeresis  |
     * | U+00D8  | ??     | O           | Latin capital letter O with stroke     |
     * | U+00D9  | ??     | U           | Latin capital letter U with grave      |
     * | U+00DA  | ??     | U           | Latin capital letter U with acute      |
     * | U+00DB  | ??     | U           | Latin capital letter U with circumflex |
     * | U+00DC  | ??     | U           | Latin capital letter U with diaeresis  |
     * | U+00DD  | ??     | Y           | Latin capital letter Y with acute      |
     * | U+00DE  | ??     | TH          | Latin capital letter Thorn             |
     * | U+00DF  | ??     | s           | Latin small letter sharp s             |
     * | U+00E0  | ??     | a           | Latin small letter a with grave        |
     * | U+00E1  | ??     | a           | Latin small letter a with acute        |
     * | U+00E2  | ??     | a           | Latin small letter a with circumflex   |
     * | U+00E3  | ??     | a           | Latin small letter a with tilde        |
     * | U+00E4  | ??     | a           | Latin small letter a with diaeresis    |
     * | U+00E5  | ??     | a           | Latin small letter a with ring above   |
     * | U+00E6  | ??     | ae          | Latin small letter ae                  |
     * | U+00E7  | ??     | c           | Latin small letter c with cedilla      |
     * | U+00E8  | ??     | e           | Latin small letter e with grave        |
     * | U+00E9  | ??     | e           | Latin small letter e with acute        |
     * | U+00EA  | ??     | e           | Latin small letter e with circumflex   |
     * | U+00EB  | ??     | e           | Latin small letter e with diaeresis    |
     * | U+00EC  | ??     | i           | Latin small letter i with grave        |
     * | U+00ED  | ??     | i           | Latin small letter i with acute        |
     * | U+00EE  | ??     | i           | Latin small letter i with circumflex   |
     * | U+00EF  | ??     | i           | Latin small letter i with diaeresis    |
     * | U+00F0  | ??     | d           | Latin small letter Eth                 |
     * | U+00F1  | ??     | n           | Latin small letter n with tilde        |
     * | U+00F2  | ??     | o           | Latin small letter o with grave        |
     * | U+00F3  | ??     | o           | Latin small letter o with acute        |
     * | U+00F4  | ??     | o           | Latin small letter o with circumflex   |
     * | U+00F5  | ??     | o           | Latin small letter o with tilde        |
     * | U+00F6  | ??     | o           | Latin small letter o with diaeresis    |
     * | U+00F8  | ??     | o           | Latin small letter o with stroke       |
     * | U+00F9  | ??     | u           | Latin small letter u with grave        |
     * | U+00FA  | ??     | u           | Latin small letter u with acute        |
     * | U+00FB  | ??     | u           | Latin small letter u with circumflex   |
     * | U+00FC  | ??     | u           | Latin small letter u with diaeresis    |
     * | U+00FD  | ??     | y           | Latin small letter y with acute        |
     * | U+00FE  | ??     | th          | Latin small letter Thorn               |
     * | U+00FF  | ??     | y           | Latin small letter y with diaeresis    |
     *
     * Decompositions for Latin Extended-A:
     *
     * |  Code   | Glyph | Replacement |                    Description                    |
     * | ------- | ----- | ----------- | ------------------------------------------------- |
     * | U+0100  | ??     | A           | Latin capital letter A with macron                |
     * | U+0101  | ??     | a           | Latin small letter a with macron                  |
     * | U+0102  | ??     | A           | Latin capital letter A with breve                 |
     * | U+0103  | ??     | a           | Latin small letter a with breve                   |
     * | U+0104  | ??     | A           | Latin capital letter A with ogonek                |
     * | U+0105  | ??     | a           | Latin small letter a with ogonek                  |
     * | U+01006 | ??     | C           | Latin capital letter C with acute                 |
     * | U+0107  | ??     | c           | Latin small letter c with acute                   |
     * | U+0108  | ??     | C           | Latin capital letter C with circumflex            |
     * | U+0109  | ??     | c           | Latin small letter c with circumflex              |
     * | U+010A  | ??     | C           | Latin capital letter C with dot above             |
     * | U+010B  | ??     | c           | Latin small letter c with dot above               |
     * | U+010C  | ??     | C           | Latin capital letter C with caron                 |
     * | U+010D  | ??     | c           | Latin small letter c with caron                   |
     * | U+010E  | ??     | D           | Latin capital letter D with caron                 |
     * | U+010F  | ??     | d           | Latin small letter d with caron                   |
     * | U+0110  | ??     | D           | Latin capital letter D with stroke                |
     * | U+0111  | ??     | d           | Latin small letter d with stroke                  |
     * | U+0112  | ??     | E           | Latin capital letter E with macron                |
     * | U+0113  | ??     | e           | Latin small letter e with macron                  |
     * | U+0114  | ??     | E           | Latin capital letter E with breve                 |
     * | U+0115  | ??     | e           | Latin small letter e with breve                   |
     * | U+0116  | ??     | E           | Latin capital letter E with dot above             |
     * | U+0117  | ??     | e           | Latin small letter e with dot above               |
     * | U+0118  | ??     | E           | Latin capital letter E with ogonek                |
     * | U+0119  | ??     | e           | Latin small letter e with ogonek                  |
     * | U+011A  | ??     | E           | Latin capital letter E with caron                 |
     * | U+011B  | ??     | e           | Latin small letter e with caron                   |
     * | U+011C  | ??     | G           | Latin capital letter G with circumflex            |
     * | U+011D  | ??     | g           | Latin small letter g with circumflex              |
     * | U+011E  | ??     | G           | Latin capital letter G with breve                 |
     * | U+011F  | ??     | g           | Latin small letter g with breve                   |
     * | U+0120  | ??     | G           | Latin capital letter G with dot above             |
     * | U+0121  | ??     | g           | Latin small letter g with dot above               |
     * | U+0122  | ??     | G           | Latin capital letter G with cedilla               |
     * | U+0123  | ??     | g           | Latin small letter g with cedilla                 |
     * | U+0124  | ??     | H           | Latin capital letter H with circumflex            |
     * | U+0125  | ??     | h           | Latin small letter h with circumflex              |
     * | U+0126  | ??     | H           | Latin capital letter H with stroke                |
     * | U+0127  | ??     | h           | Latin small letter h with stroke                  |
     * | U+0128  | ??     | I           | Latin capital letter I with tilde                 |
     * | U+0129  | ??     | i           | Latin small letter i with tilde                   |
     * | U+012A  | ??     | I           | Latin capital letter I with macron                |
     * | U+012B  | ??     | i           | Latin small letter i with macron                  |
     * | U+012C  | ??     | I           | Latin capital letter I with breve                 |
     * | U+012D  | ??     | i           | Latin small letter i with breve                   |
     * | U+012E  | ??     | I           | Latin capital letter I with ogonek                |
     * | U+012F  | ??     | i           | Latin small letter i with ogonek                  |
     * | U+0130  | ??     | I           | Latin capital letter I with dot above             |
     * | U+0131  | ??     | i           | Latin small letter dotless i                      |
     * | U+0132  | ??     | IJ          | Latin capital ligature IJ                         |
     * | U+0133  | ??     | ij          | Latin small ligature ij                           |
     * | U+0134  | ??     | J           | Latin capital letter J with circumflex            |
     * | U+0135  | ??     | j           | Latin small letter j with circumflex              |
     * | U+0136  | ??     | K           | Latin capital letter K with cedilla               |
     * | U+0137  | ??     | k           | Latin small letter k with cedilla                 |
     * | U+0138  | ??     | k           | Latin small letter Kra                            |
     * | U+0139  | ??     | L           | Latin capital letter L with acute                 |
     * | U+013A  | ??     | l           | Latin small letter l with acute                   |
     * | U+013B  | ??     | L           | Latin capital letter L with cedilla               |
     * | U+013C  | ??     | l           | Latin small letter l with cedilla                 |
     * | U+013D  | ??     | L           | Latin capital letter L with caron                 |
     * | U+013E  | ??     | l           | Latin small letter l with caron                   |
     * | U+013F  | ??     | L           | Latin capital letter L with middle dot            |
     * | U+0140  | ??     | l           | Latin small letter l with middle dot              |
     * | U+0141  | ??     | L           | Latin capital letter L with stroke                |
     * | U+0142  | ??     | l           | Latin small letter l with stroke                  |
     * | U+0143  | ??     | N           | Latin capital letter N with acute                 |
     * | U+0144  | ??     | n           | Latin small letter N with acute                   |
     * | U+0145  | ??     | N           | Latin capital letter N with cedilla               |
     * | U+0146  | ??     | n           | Latin small letter n with cedilla                 |
     * | U+0147  | ??     | N           | Latin capital letter N with caron                 |
     * | U+0148  | ??     | n           | Latin small letter n with caron                   |
     * | U+0149  | ??     | N           | Latin small letter n preceded by apostrophe       |
     * | U+014A  | ??     | n           | Latin capital letter Eng                          |
     * | U+014B  | ??     | N           | Latin small letter Eng                            |
     * | U+014C  | ??     | O           | Latin capital letter O with macron                |
     * | U+014D  | ??     | o           | Latin small letter o with macron                  |
     * | U+014E  | ??     | O           | Latin capital letter O with breve                 |
     * | U+014F  | ??     | o           | Latin small letter o with breve                   |
     * | U+0150  | ??     | O           | Latin capital letter O with double acute          |
     * | U+0151  | ??     | o           | Latin small letter o with double acute            |
     * | U+0152  | ??     | OE          | Latin capital ligature OE                         |
     * | U+0153  | ??     | oe          | Latin small ligature oe                           |
     * | U+0154  | ??     | R           | Latin capital letter R with acute                 |
     * | U+0155  | ??     | r           | Latin small letter r with acute                   |
     * | U+0156  | ??     | R           | Latin capital letter R with cedilla               |
     * | U+0157  | ??     | r           | Latin small letter r with cedilla                 |
     * | U+0158  | ??     | R           | Latin capital letter R with caron                 |
     * | U+0159  | ??     | r           | Latin small letter r with caron                   |
     * | U+015A  | ??     | S           | Latin capital letter S with acute                 |
     * | U+015B  | ??     | s           | Latin small letter s with acute                   |
     * | U+015C  | ??     | S           | Latin capital letter S with circumflex            |
     * | U+015D  | ??     | s           | Latin small letter s with circumflex              |
     * | U+015E  | ??     | S           | Latin capital letter S with cedilla               |
     * | U+015F  | ??     | s           | Latin small letter s with cedilla                 |
     * | U+0160  | ??     | S           | Latin capital letter S with caron                 |
     * | U+0161  | ??     | s           | Latin small letter s with caron                   |
     * | U+0162  | ??     | T           | Latin capital letter T with cedilla               |
     * | U+0163  | ??     | t           | Latin small letter t with cedilla                 |
     * | U+0164  | ??     | T           | Latin capital letter T with caron                 |
     * | U+0165  | ??     | t           | Latin small letter t with caron                   |
     * | U+0166  | ??     | T           | Latin capital letter T with stroke                |
     * | U+0167  | ??     | t           | Latin small letter t with stroke                  |
     * | U+0168  | ??     | U           | Latin capital letter U with tilde                 |
     * | U+0169  | ??     | u           | Latin small letter u with tilde                   |
     * | U+016A  | ??     | U           | Latin capital letter U with macron                |
     * | U+016B  | ??     | u           | Latin small letter u with macron                  |
     * | U+016C  | ??     | U           | Latin capital letter U with breve                 |
     * | U+016D  | ??     | u           | Latin small letter u with breve                   |
     * | U+016E  | ??     | U           | Latin capital letter U with ring above            |
     * | U+016F  | ??     | u           | Latin small letter u with ring above              |
     * | U+0170  | ??     | U           | Latin capital letter U with double acute          |
     * | U+0171  | ??     | u           | Latin small letter u with double acute            |
     * | U+0172  | ??     | U           | Latin capital letter U with ogonek                |
     * | U+0173  | ??     | u           | Latin small letter u with ogonek                  |
     * | U+0174  | ??     | W           | Latin capital letter W with circumflex            |
     * | U+0175  | ??     | w           | Latin small letter w with circumflex              |
     * | U+0176  | ??     | Y           | Latin capital letter Y with circumflex            |
     * | U+0177  | ??     | y           | Latin small letter y with circumflex              |
     * | U+0178  | ??     | Y           | Latin capital letter Y with diaeresis             |
     * | U+0179  | ??     | Z           | Latin capital letter Z with acute                 |
     * | U+017A  | ??     | z           | Latin small letter z with acute                   |
     * | U+017B  | ??     | Z           | Latin capital letter Z with dot above             |
     * | U+017C  | ??     | z           | Latin small letter z with dot above               |
     * | U+017D  | ??     | Z           | Latin capital letter Z with caron                 |
     * | U+017E  | ??     | z           | Latin small letter z with caron                   |
     * | U+017F  | ??     | s           | Latin small letter long s                         |
     * | U+01A0  | ??     | O           | Latin capital letter O with horn                  |
     * | U+01A1  | ??     | o           | Latin small letter o with horn                    |
     * | U+01AF  | ??     | U           | Latin capital letter U with horn                  |
     * | U+01B0  | ??     | u           | Latin small letter u with horn                    |
     * | U+01CD  | ??     | A           | Latin capital letter A with caron                 |
     * | U+01CE  | ??     | a           | Latin small letter a with caron                   |
     * | U+01CF  | ??     | I           | Latin capital letter I with caron                 |
     * | U+01D0  | ??     | i           | Latin small letter i with caron                   |
     * | U+01D1  | ??     | O           | Latin capital letter O with caron                 |
     * | U+01D2  | ??     | o           | Latin small letter o with caron                   |
     * | U+01D3  | ??     | U           | Latin capital letter U with caron                 |
     * | U+01D4  | ??     | u           | Latin small letter u with caron                   |
     * | U+01D5  | ??     | U           | Latin capital letter U with diaeresis and macron  |
     * | U+01D6  | ??     | u           | Latin small letter u with diaeresis and macron    |
     * | U+01D7  | ??     | U           | Latin capital letter U with diaeresis and acute   |
     * | U+01D8  | ??     | u           | Latin small letter u with diaeresis and acute     |
     * | U+01D9  | ??     | U           | Latin capital letter U with diaeresis and caron   |
     * | U+01DA  | ??     | u           | Latin small letter u with diaeresis and caron     |
     * | U+01DB  | ??     | U           | Latin capital letter U with diaeresis and grave   |
     * | U+01DC  | ??     | u           | Latin small letter u with diaeresis and grave     |
     *
     * Decompositions for Latin Extended-B:
     *
     * |   Code   | Glyph | Replacement |                Description                |
     * | -------- | ----- | ----------- | ----------------------------------------- |
     * | U+0218   | ??     | S           | Latin capital letter S with comma below   |
     * | U+0219   | ??     | s           | Latin small letter s with comma below     |
     * | U+021A   | ??     | T           | Latin capital letter T with comma below   |
     * | U+021B   | ??     | t           | Latin small letter t with comma below     |
     *
     * Vowels with diacritic (Chinese, Hanyu Pinyin):
     *
     * |   Code   | Glyph | Replacement |                      Description                      |
     * | -------- | ----- | ----------- | ----------------------------------------------------- |
     * | U+0251   | ??     | a           | Latin small letter alpha                              |
     * | U+1EA0   | ???     | A           | Latin capital letter A with dot below                 |
     * | U+1EA1   | ???     | a           | Latin small letter a with dot below                   |
     * | U+1EA2   | ???     | A           | Latin capital letter A with hook above                |
     * | U+1EA3   | ???     | a           | Latin small letter a with hook above                  |
     * | U+1EA4   | ???     | A           | Latin capital letter A with circumflex and acute      |
     * | U+1EA5   | ???     | a           | Latin small letter a with circumflex and acute        |
     * | U+1EA6   | ???     | A           | Latin capital letter A with circumflex and grave      |
     * | U+1EA7   | ???     | a           | Latin small letter a with circumflex and grave        |
     * | U+1EA8   | ???     | A           | Latin capital letter A with circumflex and hook above |
     * | U+1EA9   | ???     | a           | Latin small letter a with circumflex and hook above   |
     * | U+1EAA   | ???     | A           | Latin capital letter A with circumflex and tilde      |
     * | U+1EAB   | ???     | a           | Latin small letter a with circumflex and tilde        |
     * | U+1EA6   | ???     | A           | Latin capital letter A with circumflex and dot below  |
     * | U+1EAD   | ???     | a           | Latin small letter a with circumflex and dot below    |
     * | U+1EAE   | ???     | A           | Latin capital letter A with breve and acute           |
     * | U+1EAF   | ???     | a           | Latin small letter a with breve and acute             |
     * | U+1EB0   | ???     | A           | Latin capital letter A with breve and grave           |
     * | U+1EB1   | ???     | a           | Latin small letter a with breve and grave             |
     * | U+1EB2   | ???     | A           | Latin capital letter A with breve and hook above      |
     * | U+1EB3   | ???     | a           | Latin small letter a with breve and hook above        |
     * | U+1EB4   | ???     | A           | Latin capital letter A with breve and tilde           |
     * | U+1EB5   | ???     | a           | Latin small letter a with breve and tilde             |
     * | U+1EB6   | ???     | A           | Latin capital letter A with breve and dot below       |
     * | U+1EB7   | ???     | a           | Latin small letter a with breve and dot below         |
     * | U+1EB8   | ???     | E           | Latin capital letter E with dot below                 |
     * | U+1EB9   | ???     | e           | Latin small letter e with dot below                   |
     * | U+1EBA   | ???     | E           | Latin capital letter E with hook above                |
     * | U+1EBB   | ???     | e           | Latin small letter e with hook above                  |
     * | U+1EBC   | ???     | E           | Latin capital letter E with tilde                     |
     * | U+1EBD   | ???     | e           | Latin small letter e with tilde                       |
     * | U+1EBE   | ???     | E           | Latin capital letter E with circumflex and acute      |
     * | U+1EBF   | ???     | e           | Latin small letter e with circumflex and acute        |
     * | U+1EC0   | ???     | E           | Latin capital letter E with circumflex and grave      |
     * | U+1EC1   | ???     | e           | Latin small letter e with circumflex and grave        |
     * | U+1EC2   | ???     | E           | Latin capital letter E with circumflex and hook above |
     * | U+1EC3   | ???     | e           | Latin small letter e with circumflex and hook above   |
     * | U+1EC4   | ???     | E           | Latin capital letter E with circumflex and tilde      |
     * | U+1EC5   | ???     | e           | Latin small letter e with circumflex and tilde        |
     * | U+1EC6   | ???     | E           | Latin capital letter E with circumflex and dot below  |
     * | U+1EC7   | ???     | e           | Latin small letter e with circumflex and dot below    |
     * | U+1EC8   | ???     | I           | Latin capital letter I with hook above                |
     * | U+1EC9   | ???     | i           | Latin small letter i with hook above                  |
     * | U+1ECA   | ???     | I           | Latin capital letter I with dot below                 |
     * | U+1ECB   | ???     | i           | Latin small letter i with dot below                   |
     * | U+1ECC   | ???     | O           | Latin capital letter O with dot below                 |
     * | U+1ECD   | ???     | o           | Latin small letter o with dot below                   |
     * | U+1ECE   | ???     | O           | Latin capital letter O with hook above                |
     * | U+1ECF   | ???     | o           | Latin small letter o with hook above                  |
     * | U+1ED0   | ???     | O           | Latin capital letter O with circumflex and acute      |
     * | U+1ED1   | ???     | o           | Latin small letter o with circumflex and acute        |
     * | U+1ED2   | ???     | O           | Latin capital letter O with circumflex and grave      |
     * | U+1ED3   | ???     | o           | Latin small letter o with circumflex and grave        |
     * | U+1ED4   | ???     | O           | Latin capital letter O with circumflex and hook above |
     * | U+1ED5   | ???     | o           | Latin small letter o with circumflex and hook above   |
     * | U+1ED6   | ???     | O           | Latin capital letter O with circumflex and tilde      |
     * | U+1ED7   | ???     | o           | Latin small letter o with circumflex and tilde        |
     * | U+1ED8   | ???     | O           | Latin capital letter O with circumflex and dot below  |
     * | U+1ED9   | ???     | o           | Latin small letter o with circumflex and dot below    |
     * | U+1EDA   | ???     | O           | Latin capital letter O with horn and acute            |
     * | U+1EDB   | ???     | o           | Latin small letter o with horn and acute              |
     * | U+1EDC   | ???     | O           | Latin capital letter O with horn and grave            |
     * | U+1EDD   | ???     | o           | Latin small letter o with horn and grave              |
     * | U+1EDE   | ???     | O           | Latin capital letter O with horn and hook above       |
     * | U+1EDF   | ???     | o           | Latin small letter o with horn and hook above         |
     * | U+1EE0   | ???     | O           | Latin capital letter O with horn and tilde            |
     * | U+1EE1   | ???     | o           | Latin small letter o with horn and tilde              |
     * | U+1EE2   | ???     | O           | Latin capital letter O with horn and dot below        |
     * | U+1EE3   | ???     | o           | Latin small letter o with horn and dot below          |
     * | U+1EE4   | ???     | U           | Latin capital letter U with dot below                 |
     * | U+1EE5   | ???     | u           | Latin small letter u with dot below                   |
     * | U+1EE6   | ???     | U           | Latin capital letter U with hook above                |
     * | U+1EE7   | ???     | u           | Latin small letter u with hook above                  |
     * | U+1EE8   | ???     | U           | Latin capital letter U with horn and acute            |
     * | U+1EE9   | ???     | u           | Latin small letter u with horn and acute              |
     * | U+1EEA   | ???     | U           | Latin capital letter U with horn and grave            |
     * | U+1EEB   | ???     | u           | Latin small letter u with horn and grave              |
     * | U+1EEC   | ???     | U           | Latin capital letter U with horn and hook above       |
     * | U+1EED   | ???     | u           | Latin small letter u with horn and hook above         |
     * | U+1EEE   | ???     | U           | Latin capital letter U with horn and tilde            |
     * | U+1EEF   | ???     | u           | Latin small letter u with horn and tilde              |
     * | U+1EF0   | ???     | U           | Latin capital letter U with horn and dot below        |
     * | U+1EF1   | ???     | u           | Latin small letter u with horn and dot below          |
     * | U+1EF2   | ???     | Y           | Latin capital letter Y with grave                     |
     * | U+1EF3   | ???     | y           | Latin small letter y with grave                       |
     * | U+1EF4   | ???     | Y           | Latin capital letter Y with dot below                 |
     * | U+1EF5   | ???     | y           | Latin small letter y with dot below                   |
     * | U+1EF6   | ???     | Y           | Latin capital letter Y with hook above                |
     * | U+1EF7   | ???     | y           | Latin small letter y with hook above                  |
     * | U+1EF8   | ???     | Y           | Latin capital letter Y with tilde                     |
     * | U+1EF9   | ???     | y           | Latin small letter y with tilde                       |
     *
     * German (`de_DE`), German formal (`de_DE_formal`), German (Switzerland) formal (`de_CH`),
     * and German (Switzerland) informal (`de_CH_informal`) locales:
     *
     * |   Code   | Glyph | Replacement |               Description               |
     * | -------- | ----- | ----------- | --------------------------------------- |
     * | U+00C4   | ??     | Ae          | Latin capital letter A with diaeresis   |
     * | U+00E4   | ??     | ae          | Latin small letter a with diaeresis     |
     * | U+00D6   | ??     | Oe          | Latin capital letter O with diaeresis   |
     * | U+00F6   | ??     | oe          | Latin small letter o with diaeresis     |
     * | U+00DC   | ??     | Ue          | Latin capital letter U with diaeresis   |
     * | U+00FC   | ??     | ue          | Latin small letter u with diaeresis     |
     * | U+00DF   | ??     | ss          | Latin small letter sharp s              |
     *
     * Danish (`da_DK`) locale:
     *
     * |   Code   | Glyph | Replacement |               Description               |
     * | -------- | ----- | ----------- | --------------------------------------- |
     * | U+00C6   | ??     | Ae          | Latin capital letter AE                 |
     * | U+00E6   | ??     | ae          | Latin small letter ae                   |
     * | U+00D8   | ??     | Oe          | Latin capital letter O with stroke      |
     * | U+00F8   | ??     | oe          | Latin small letter o with stroke        |
     * | U+00C5   | ??     | Aa          | Latin capital letter A with ring above  |
     * | U+00E5   | ??     | aa          | Latin small letter a with ring above    |
     *
     * Catalan (`ca`) locale:
     *
     * |   Code   | Glyph | Replacement |               Description               |
     * | -------- | ----- | ----------- | --------------------------------------- |
     * | U+00B7   | l??l   | ll          | Flown dot (between two Ls)              |
     *
     * @since 1.2.1
     * @since 4.6.0 Added locale support for `de_CH`, `de_CH_informal`, and `ca`.
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    static function remove_accents( $string ) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        if (self::seems_utf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
                chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
                chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
                chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
                chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
                chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
                chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
                chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
                chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
                chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
                chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
                chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
                chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
                chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
                chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
                chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
                chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
                chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
                chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
                chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
                chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
                chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
                chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
                chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
                chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
                chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
                chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
                chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
                chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
                chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
                chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
                chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
                // Decompositions for Latin Extended-A
                chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
                chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
                chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
                chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
                chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
                chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
                chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
                chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
                chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
                chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
                chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
                chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
                chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
                chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
                chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
                chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
                chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
                chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
                chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
                chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
                chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
                chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
                chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
                chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
                chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
                chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
                chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
                chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
                chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
                chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
                chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
                chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
                chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
                chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
                chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
                chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
                chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
                chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
                chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
                chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
                chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
                chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
                chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
                chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
                chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
                chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
                chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
                chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
                chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
                chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
                chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
                chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
                chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
                chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
                chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
                chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
                chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
                chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
                chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
                chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
                chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
                chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
                chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
                chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
                // Decompositions for Latin Extended-B
                chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
                chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
                // Euro Sign
                chr(226).chr(130).chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194).chr(163) => '',
                // Vowels with diacritic (Vietnamese)
                // unmarked
                chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
                chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
                // grave accent
                chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
                chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
                chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
                chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
                chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
                chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
                chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
                // hook
                chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
                chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
                chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
                chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
                chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
                chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
                chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
                chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
                chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
                chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
                chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
                chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
                // tilde
                chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
                chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
                chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
                chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
                chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
                chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
                chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
                chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
                // acute accent
                chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
                chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
                chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
                chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
                chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
                chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
                // dot below
                chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
                chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
                chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
                chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
                chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
                chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
                chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
                chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
                chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
                chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
                chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
                chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
                // Vowels with diacritic (Chinese, Hanyu Pinyin)
                chr(201).chr(145) => 'a',
                // macron
                chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
                // acute accent
                chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
                // caron
                chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
                chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
                chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
                chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
                chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
                // grave accent
                chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
            );

            $string = strtr($string, $chars);
        } else {
            $chars = array();
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
                .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
                .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
                .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
                .chr(252).chr(253).chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars = array();
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    /**
     * Code taken from David Walsh Site
     * @author David Walsh
     * https://davidwalsh.name/flatten-nested-arrays-php
     * @param $array
     * @param $return
     * @return array
     */
    static function array_flatten($array,$return) {
        for($x = 0; $x < count($array); $x++) {
            if(is_array($array[$x])) {
                $return = self::array_flatten($array[$x], $return);
            }
            else {
                if(isset($array[$x])) {
                    $return[] = $array[$x];
                }
            }
        }

        return $return;
    }
}