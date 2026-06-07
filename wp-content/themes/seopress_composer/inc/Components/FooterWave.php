<?php

namespace SeopressComposer\Components;

class FooterWave
{
  public function render(): string
  {
    ob_start();
?>
    <div id="shopify-section-sections--15361934360678__ss_wave_2_MpeJkT" class="shopify-section shopify-section-group-footer-group">
      <style data-shopify="">
        .section-sections--15361934360678__ss_wave_2_MpeJkT {
          border-top: solid #000000 0px;
          border-bottom: solid #000000 0px;
          margin-top: 0px;
          margin-bottom: 0px;
        }

        .section-sections--15361934360678__ss_wave_2_MpeJkT-settings {
          margin: 0 auto;
          padding-top: 0px;
          padding-bottom: 0px;
          padding-left: 0rem;
          padding-right: 0rem;
        }

        .wave-item-sections--15361934360678__ss_wave_2_MpeJkT svg {
          display: block;
          width: 100%;
          height: auto;
          overflow: hidden;
          max-height: 40px;
        }

        .wave-parallax2-sections--15361934360678__ss_wave_2_MpeJkT>use {
          opacity: .4;
        }

        .wave-parallax3-sections--15361934360678__ss_wave_2_MpeJkT>use {
          opacity: .3;
        }

        .wave-parallax4-sections--15361934360678__ss_wave_2_MpeJkT>use {
          opacity: .2;
        }

        @media(min-width: 1024px) {

          .section-sections--15361934360678__ss_wave_2_MpeJkT {
            margin-top: 0px;
            margin-bottom: 0px;
          }

          .section-sections--15361934360678__ss_wave_2_MpeJkT-settings {
            padding: 0 5rem;
            padding-top: 0px;
            padding-bottom: 0px;
            padding-left: 0rem;
            padding-right: 0rem;
          }

          .wave-item-sections--15361934360678__ss_wave_2_MpeJkT svg {
            max-height: 60px;
          }
        }
      </style>




      <style>
        .wave-parallax1-sections--15361934360678__ss_wave_2_MpeJkT>use {
          animation: move-forever1sections--15361934360678__ss_wave_2_MpeJkT 10s linear infinite;
        }

        .wave-parallax2-sections--15361934360678__ss_wave_2_MpeJkT>use {
          animation: move-forever2sections--15361934360678__ss_wave_2_MpeJkT 10s linear infinite;
        }

        .wave-parallax3-sections--15361934360678__ss_wave_2_MpeJkT>use {
          animation: move-forever3sections--15361934360678__ss_wave_2_MpeJkT 10s linear infinite;
        }

        .wave-parallax3-sections--15361934360678__ss_wave_2_MpeJkT>use {
          animation: move-forever4sections--15361934360678__ss_wave_2_MpeJkT 10s linear infinite;
        }

        @keyframes move-forever1sections--15361934360678__ss_wave_2_MpeJkT {
          0% {
            transform: translate(85px);
          }

          100% {
            transform: translate(-90px);
          }
        }

        @keyframes move-forever2sections--15361934360678__ss_wave_2_MpeJkT {
          0% {
            transform: translate(-90px);
          }

          100% {
            transform: translate(85px);
          }
        }

        @keyframes move-forever3sections--15361934360678__ss_wave_2_MpeJkT {
          0% {
            transform: translate(85px);
          }

          100% {
            transform: translate(-90px);
          }
        }

        @keyframes move-forever4sections--15361934360678__ss_wave_2_MpeJkT {
          0% {
            transform: translate(-90px);
          }

          100% {
            transform: translate(85px);
          }
        }
      </style>


      <div class="section-sections--15361934360678__ss_wave_2_MpeJkT wave-sections--15361934360678__ss_wave_2_MpeJkT bg-secondary">
        <div class="section-sections--15361934360678__ss_wave_2_MpeJkT-settings">
          <div class="wave-item-sections--15361934360678__ss_wave_2_MpeJkT text-background-primary">
            <svg class="waves-animated-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none">
              <defs>
                <path id="gentle-wave" d="M-160 44c30 0
              58-18 88-18s
              58 18 88 18
              58-18 88-18
              58 18 88 18
              v44h-352z"></path>
              </defs>
              <g class="wave-parallax1-sections--15361934360678__ss_wave_2_MpeJkT">
                <use xlink:href="#gentle-wave" x="50" y="3" fill="currentColor"></use>
              </g>
              <g class="wave-parallax2-sections--15361934360678__ss_wave_2_MpeJkT">
                <use xlink:href="#gentle-wave" x="50" y="0" fill="currentColor"></use>
              </g>
              <g class="wave-parallax3-sections--15361934360678__ss_wave_2_MpeJkT">
                <use xlink:href="#gentle-wave" x="50" y="9" fill="currentColor"></use>
              </g>
              <g class="wave-parallax4-sections--15361934360678__ss_wave_2_MpeJkT">
                <use xlink:href="#gentle-wave" x="50" y="6" fill="currentColor"></use>
              </g>
            </svg>
          </div>
        </div>
      </div>

    </div>
<?php
    return ob_get_clean();
  }
}
