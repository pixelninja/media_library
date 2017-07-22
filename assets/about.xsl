<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xlink="http://www.w3.org/1999/xlink">

	<xsl:template match="entry" mode="about">

        <div class="col-2-3 flexbox flex-wrap flex-content-center" data-parallax="1" data-parallax-mobile="no">
            <div class="title col-full">
				<h1>
	                <xsl:call-template name="format-date">
	                    <xsl:with-param name="date" select="$start-date" />
	                    <xsl:with-param name="format" select="'%0d;%ds;'" />
	                </xsl:call-template>

	                <xsl:text> - </xsl:text>

	                <xsl:call-template name="format-date">
	                    <xsl:with-param name="date" select="$end-date" />
	                    <xsl:with-param name="format" select="'%0d;%ds; %m+; %y+;'" />
	                </xsl:call-template>
	            </h1>
            </div>

			<span class="countdown-timer" data-start="{$start-date/@iso}">0 days 00:00:00</span>

            <xsl:apply-templates select="intro/*" mode="output" />
        </div>

        <div class="col-2-4 offset neg-left-1 load-effect">
            <img src="{$workspace}{image/@path}/{image/filename}" alt="{$website-name}" />
        </div>

        <div class="copy block-wrapper flexbox flex-wrap flex-items-end">
			<div class="col-2-3">
	            <xsl:apply-templates select="copy/*" mode="output" />
			</div>

			<div class="col-1-3">
				<a class="btn" rel="external" href="{$register}" data-parallax="0.3" data-parallax-mobile="no">Register Here</a>
			</div>

			<div class="block col-2-4" data-parallax="2"></div>
        </div>
	</xsl:template>

	<xsl:template match="entry" mode="quotes">
        <img src="{$workspace}{image/@path}/{image/filename}" alt="{$website-name}" data-parallax="1.5"  />
		<!-- <xsl:attribute name="style">
			<xsl:text>background-image: url(</xsl:text>
			<xsl:value-of select="concat($workspace, image/@path, '/', image/filename)" />
			<xsl:text>)</xsl:text>
		</xsl:attribute> -->

        <article class="section">
            <xsl:apply-templates select="quotes/item" mode="quote" />
        </article>
	</xsl:template>

	<xsl:template match="item" mode="quote">
		<xsl:variable name="entry" select="/data/quotes-read-by-id/entry[@id = current()/@id]" />

		<div class="quote">
	        <p>
				<xsl:value-of select="$entry/quote" />
				<span>
					<xsl:value-of select="$entry/author" />
				</span>
			</p>
		</div>
	</xsl:template>

</xsl:stylesheet>
