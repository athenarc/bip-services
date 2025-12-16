if (typeof spaceColor !== 'undefined') {
    const lightColor = tinycolor(spaceColor).lighten(30).toHexString();
    const darkColor = tinycolor(spaceColor).darken(10).toHexString();
    const transparentChartColor = `${spaceColor}${Math.round(255 * 0.15).toString(16)}`;
    document.documentElement.style.setProperty('--main-color', spaceColor);
    document.documentElement.style.setProperty('--lighter-color', lightColor);
    document.documentElement.style.setProperty('--darker-color', darkColor);
    document.documentElement.style.setProperty('--transparent-color', transparentChartColor);
}
