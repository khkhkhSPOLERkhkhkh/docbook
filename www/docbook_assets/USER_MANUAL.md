Docbook : user manual
===================================

An every day manual on how to use **DocBook** and **Markdown**.


## About DocBook

#### Organization

A classic DocBook directory organization should be:

    | chapter-name/
    | ------------- README.md           // the first file shown loading the directory
    | ------------- PAGE.md             // a Markdown content file (page 1)
    | ------------- OTHER-PAGE.md       // another Markdown content file (page 2)
    | ------------- OTHER-PAGE.fr.md    // the french translation of page 2
    | ------------- ...                 // as many pages as you like
    | ------------- sub-chapter1/       // a sub-directory containing a sub-chapter
    | --------------------------- ...   // your sub-chapter 1 pages and assets ...
    | ------------- sub-chapter2/       // a sub-directory containing another sub-chapter
    | --------------------------- ...   // your sub-chapter 2 pages and assets ...
    | ------------- assets/             // a directory containing your medias
    | --------------------- image1.png  
    | --------------------- image2.gif  
    | --------------------- ...  
    | ------------- wip/                // a directory containing your work-in-progress contents
    | ------------------ WorkInProgress-page.md
    | ------------------ ...  

#### DocBook pages structure

Each page of DocBook has a top menu that, in most cases depending on your device, will stay
static at the top of your window. It contains the global menu of your first hierarchy chapters
and some tools to go to the top and bottom of the page, and a menu of your current contents.

Then, if you are seeing a directory, the first table lists its structure: pages and sub-directories.
The content of the current directory "README" file is shown below if found.

Clicking on a page's link will let you visualize the content of the page.


## About Markdown


#### Titles

    # my title level 1
    ### my title level 3

#### Paragraphs

Just pass a blank line ...

#### Pre-formatted

Begin lines with 4 spaces (example this block)

        pre formed content

#### Blockquotes and citations

Begin lines by '>'

    > my citation

#### Horizontal rule

3 or more hyphens, asterisks or underscores on a line

    ----

#### Bold text

    **bolded content**
        or
    __bolded content__

#### Italic text

    *italic content*
        or
    _italic content_

#### A code span

    `function()`

#### Links

Automatic links:

    <http://example.com/>
        and
    <address@email.com>

An hypertext link:

    [link text](http://example.com/ "Optional link title")

A referenced hypertext link:

    [link text] [myid]
        and after the paragraph, anywhere in the document
    [myid]: http://example.com/ "Optional link title"

#### Images

An embedded image:

    ![Alt text](http://upload.wikimedia.org/wikipedia/commons/7/70/Example.png "Optional image title")

A referrenced embedded image:

    ![Alt text][myimageid]
        and after the paragraph, anywhere in the document
    [myimageid]: http://upload.wikimedia.org/wikipedia/commons/7/70/Example.png "Optional image title"

#### A list

Begin each entry by an asterisk, a plus or an hyphen followed by 3 spaces

    -   first item
    *   second item

For an ordered list, begin each entry by a number followed by a dot and 3 spaces

    1.   first item
    1.   second item

#### Fenced code block

A line of tildes (at least 3)

    ~~~~
    My code here
    ~~~~

#### Table:

    | First Header  | Second Header |
    | ------------- | ------------: |
    | Content Cell  | Content Cell  |
    | Content Cell  | Content Cell  |

or (without leading pipe) :

    First Header  | Second Header |
    ------------- | ------------: |
    Content Cell  | Content Cell  |
    Content Cell  | Content Cell  |

or (not constant spaces) :

    | First Header | Second Header |
    | ------------ | ------------: |
    | Cell | Cell |
    | Cell | Cell |

#### Definition

    Term 1
    :   This is a definition with two paragraphs. Lorem ipsum 
        dolor sit amet, consectetuer adipiscing elit. Aliquam 
        hendrerit mi posuere lectus.

        Vestibulum enim wisi, viverra nec, fringilla in, laoreet
        vitae, risus.

    :   Second definition for term 1, also wrapped in a paragraph
        because of the blank line preceding it.

#### Footnote

    That's some text with a footnote.[^1]

    [^1]: And that's the footnote.

#### Abbreviation

    *[HTML]: Hyper Text Markup Language

#### Citations

Like a footnote begining by a sharp

    This is a statement that should be attributed to
    its source[p. 23][#Doe:2006].

    And following is the description of the reference to be
    used in the bibliography.

    [#Doe:2006]: John Doe. *Some Big Fancy Book*.  Vanity Press, 2006.

#### Glossary

    [^glossaryfootnote]: glossary: term (optional sort key)
        The actual definition belongs on a new line, and can continue on
        just as other footnotes.
