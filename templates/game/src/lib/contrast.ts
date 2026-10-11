type RGB = [number, number, number];

function hexToRgb(hex: string): RGB {
  return [
    parseInt(hex.slice(1, 3), 16),
    parseInt(hex.slice(3, 5), 16),
    parseInt(hex.slice(5, 7), 16),
  ];
}

function linearChannel(value: number): number {
  const channel = value / 255;

  return channel <= 0.04045
    ? channel / 12.92
    : ((channel + 0.055) / 1.055) ** 2.4;
}

function luminance(rgb: RGB): number {
  const red = linearChannel(rgb[0]);
  const green = linearChannel(rgb[1]);
  const blue = linearChannel(rgb[2]);

  return (
    0.2126 * red +
    0.7152 * green +
    0.0722 * blue
  );
}

export function contrastRatio(
  foreground: string,
  background: string,
): number {
  const a = luminance(hexToRgb(foreground));
  const b = luminance(hexToRgb(background));

  const lighter = Math.max(a, b);
  const darker = Math.min(a, b);

  return (lighter + 0.05) / (darker + 0.05);
}

export function blendHex(
  foreground: string,
  background: string,
  opacity: number,
): string {
  const front = hexToRgb(foreground);
  const back = hexToRgb(background);

  const channels = front.map((value, index) => {
    const result = Math.round(
      value * opacity + back[index]! * (1 - opacity),
    );

    return result.toString(16).padStart(2, '0');
  });

  return `#${channels.join('')}`;
}
