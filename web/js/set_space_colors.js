if (typeof spaceColor !== 'undefined') {
    var lightColor = tinycolor(spaceColor).lighten(30).toHexString();
    var darkColor = tinycolor(spaceColor).darken(10).toHexString();
    var transparentChartColor = `${spaceColor}${Math.round(255 * 0.15).toString(16)}`;
    document.documentElement.style.setProperty('--main-color', spaceColor);
    document.documentElement.style.setProperty('--lighter-color', lightColor);
    document.documentElement.style.setProperty('--darker-color', darkColor);
    document.documentElement.style.setProperty('--transparent-color', transparentChartColor);
}