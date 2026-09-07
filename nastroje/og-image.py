#!/usr/bin/env python3
"""
Vygeneruje náhledový obrázek odkazu (Open Graph) do assets/og/uvolni-to-og.jpg.

Spuštění z kořene repa:   python nastroje/og-image.py
Potřebuje Pillow:         pip install Pillow

Proč skript a ne ruční export: až se vymění fotka nebo logo, stačí přepsat
FOTKA níž a pustit tohle znovu — kompozice, ořez i barvy zůstanou stejné.

Rozměr 1200x630 je poměr 1,91:1, který chtějí Facebook, Messenger, WhatsApp,
Slack, Discord i Twitter/X. Menší než 600x315 sítě odmítají, větší zbytečně
zdržuje načtení náhledu.
"""
from PIL import Image, ImageDraw, ImageFilter

FOTKA   = 'assets/foto/WhatsApp-Image-2026-03-08-at-17.44.40.jpeg'
LOGO    = 'assets/logo/ODVOZENO-wordmark-kremove.png'   # krémové = na tmavé
VYSTUP  = 'assets/og/uvolni-to-og.jpg'
OHNISKO = 0.40          # 0 = ořez od horního okraje, 1 = od spodního
NADECH  = 0.30          # jak moc se fotka přelije značkovou zelenou

W, H   = 1200, 630
FOREST = (34, 58, 52)                # --c-forest-dk
SAND   = (203, 187, 160, 120)        # --c-sand, poloprůhledná


def main():
    src = Image.open(FOTKA).convert('RGB')

    # ── ořez „cover“ na 1200x630 ──────────────────────────────────────────
    sw, sh = src.size
    scale = max(W / sw, H / sh)
    nw, nh = round(sw * scale), round(sh * scale)
    im = src.resize((nw, nh), Image.LANCZOS)
    top = max(0, min(nh - H, round(nh * OHNISKO - H / 2)))
    im = im.crop(((nw - W) // 2, top, (nw - W) // 2 + W, top + H))

    # ── značkový nádech ───────────────────────────────────────────────────
    im = Image.blend(im, Image.new('RGB', (W, H), FOREST), NADECH)

    # ── svislý gradient: dole tmavší ──────────────────────────────────────
    g = Image.new('L', (1, H))
    for y in range(H):
        g.putpixel((0, y), int(255 * (0.14 + 0.46 * (y / (H - 1)) ** 1.7)))
    im = Image.composite(Image.new('RGB', (W, H), FOREST), im, g.resize((W, H)))

    # ── měkký stín pod logem ──────────────────────────────────────────────
    # Bez něj logo mizí všude, kde je pod ním světlé místo fotky. Takhle drží
    # kontrast bez ohledu na to, jakou fotku někdo příště dosadí.
    hal = Image.new('L', (W, H), 0)
    ImageDraw.Draw(hal).ellipse((W * .16, H * .20, W * .84, H * .80), fill=170)
    im = Image.composite(Image.new('RGB', (W, H), FOREST), im,
                         hal.filter(ImageFilter.GaussianBlur(90)))

    # ── vinětace ──────────────────────────────────────────────────────────
    v = Image.new('L', (W, H), 0)
    ImageDraw.Draw(v).ellipse((-W * .3, -H * .45, W * 1.3, H * 1.45), fill=255)
    v = v.filter(ImageFilter.GaussianBlur(150)).point(lambda x: 255 - int(x * .34))
    im = Image.composite(Image.new('RGB', (W, H), FOREST), im, v)

    # ── logo a vlásečnice po stranách (stejný motiv jako .rule na webu) ───
    lg = Image.open(LOGO).convert('RGBA')
    lw = 470
    lh = round(lg.height * lw / lg.width)
    lg = lg.resize((lw, lh), Image.LANCZOS)
    lx, ly = (W - lw) // 2, (H - lh) // 2 - 10
    im = im.convert('RGBA')
    im.alpha_composite(lg, (lx, ly))
    d = ImageDraw.Draw(im, 'RGBA')
    cy = ly + lh // 2
    d.line((int(W * .11), cy, lx - 46, cy), fill=SAND, width=1)
    d.line((lx + lw + 46, cy, int(W * .89), cy), fill=SAND, width=1)

    im.convert('RGB').save(VYSTUP, quality=88, optimize=True)
    print(f'hotovo: {VYSTUP}  {W}x{H}')


if __name__ == '__main__':
    main()
